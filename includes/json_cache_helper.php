<?php

class JsonCacheSystem {
    private $cacheDir;
    private $defaultTtl;
    private $fileLockTimeout;
    private $gcProbability = 100; // 1 in 100 chance to run garbage collection
    
    /**
     * @param string $cacheDir Directory to store cache files
     * @param int $defaultTtl Default time-to-live in seconds (0 = infinite)
     * @param int $fileLockTimeout Timeout for file locks in seconds
     */
    public function __construct(string $cacheDir = __DIR__ . '/cache', int $defaultTtl = 3600, int $fileLockTimeout = 5) {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->defaultTtl = $defaultTtl;
        $this->fileLockTimeout = $fileLockTimeout;
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Store data in cache
     * @param string $key Cache key
     * @param mixed $data Data to store
     * @param int|null $ttl Time-to-live in seconds (null uses default)
     * @param array $tags Array of tags for this cache item
     * @return bool True on success
     */
    public function set(string $key, $data, ?int $ttl = null, array $tags = []): bool {
        $this->validateKey($key);
        
        $ttl = $ttl ?? $this->defaultTtl;
        $expiresAt = $ttl > 0 ? time() + $ttl : null;
        
        $cacheFile = $this->getCacheFilePath($key);
        $tempFile = $cacheFile . '.' . uniqid('', true) . '.tmp';
        
        $cacheData = [
            'data' => $data,
            'meta' => [
                'created_at' => time(),
                'expires_at' => $expiresAt,
                'tags' => $tags,
                'version' => 1,
                'key' => $key
            ]
        ];
        
        $json = json_encode($cacheData, JSON_PRETTY_PRINT);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to encode cache data: ' . json_last_error_msg());
        }
        
        // Atomic write using temporary file
        if (file_put_contents($tempFile, $json, LOCK_EX) === false) {
            @unlink($tempFile);
            return false;
        }
        
        // Lock the cache directory while moving the file
        $lock = $this->acquireLock($key);
        $success = rename($tempFile, $cacheFile);
        $this->releaseLock($lock);
        
        if (!$success) {
            @unlink($tempFile);
            return false;
        }
        
        // Occasionally run garbage collection
        if (random_int(1, $this->gcProbability) === 1) {
            $this->collectGarbage();
        }
        
        return true;
    }
    
    /**
     * Get data from cache
     * @param string $key Cache key
     * @param mixed $default Default value if cache miss
     * @return mixed Cached data or default
     */
    public function get(string $key, $default = null) {
        $this->validateKey($key);
        
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return $default;
        }
        
        $lock = $this->acquireLock($key);
        
        try {
            $content = file_get_contents($cacheFile);
            if ($content === false) {
                $this->releaseLock($lock);
                return $default;
            }
            
            $cacheData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->releaseLock($lock);
                @unlink($cacheFile);
                return $default;
            }
            
            // Check expiration
            if (isset($cacheData['meta']['expires_at']) && 
                $cacheData['meta']['expires_at'] < time()) {
                $this->releaseLock($lock);
                @unlink($cacheFile);
                return $default;
            }
            
            $this->releaseLock($lock);
            return $cacheData['data'];
        } catch (Exception $e) {
            $this->releaseLock($lock);
            return $default;
        }
    }
    
    /**
     * Check if cache key exists and is valid
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool {
        $this->validateKey($key);
        
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $lock = $this->acquireLock($key);
        
        try {
            $content = file_get_contents($cacheFile);
            if ($content === false) {
                $this->releaseLock($lock);
                return false;
            }
            
            $cacheData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->releaseLock($lock);
                return false;
            }
            
            // Check expiration
            if (isset($cacheData['meta']['expires_at']) && 
                $cacheData['meta']['expires_at'] < time()) {
                $this->releaseLock($lock);
                return false;
            }
            
            $this->releaseLock($lock);
            return true;
        } catch (Exception $e) {
            $this->releaseLock($lock);
            return false;
        }
    }
    
    /**
     * Delete a cache item
     * @param string $key Cache key
     * @return bool True if deleted or didn't exist
     */
    public function delete(string $key): bool {
        $this->validateKey($key);
        
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return true;
        }
        
        $lock = $this->acquireLock($key);
        $success = @unlink($cacheFile);
        $this->releaseLock($lock);
        
        return $success;
    }
    
    /**
     * Invalidate cache by tags
     * @param array|string $tags Tag or array of tags
     * @return int Number of items invalidated
     */
    public function invalidateTags($tags): int {
        $tags = is_array($tags) ? $tags : [$tags];
        $count = 0;
        
        $files = glob($this->cacheDir . '*.json');
        foreach ($files as $file) {
            $lock = $this->acquireLock(basename($file, '.json'));
            
            try {
                $content = file_get_contents($file);
                if ($content === false) continue;
                
                $cacheData = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) continue;
                
                if (!empty(array_intersect($tags, $cacheData['meta']['tags'] ?? []))) {
                    if (@unlink($file)) {
                        $count++;
                    }
                }
            } finally {
                $this->releaseLock($lock);
            }
        }
        
        return $count;
    }
    
    /**
     * Clear all cache
     * @return int Number of items cleared
     */
    public function clearAll(): int {
        $count = 0;
        $files = glob($this->cacheDir . '*.json');
        
        foreach ($files as $file) {
            $key = basename($file, '.json');
            $lock = $this->acquireLock($key);
            
            if (@unlink($file)) {
                $count++;
            }
            
            $this->releaseLock($lock);
        }
        
        return $count;
    }
    
    /**
     * Get cache item metadata
     * @param string $key Cache key
     * @return array|null Metadata or null if not found
     */
    public function getMeta(string $key): ?array {
        $this->validateKey($key);
        
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $lock = $this->acquireLock($key);
        
        try {
            $content = file_get_contents($cacheFile);
            if ($content === false) {
                $this->releaseLock($lock);
                return null;
            }
            
            $cacheData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->releaseLock($lock);
                return null;
            }
            
            $this->releaseLock($lock);
            return $cacheData['meta'];
        } catch (Exception $e) {
            $this->releaseLock($lock);
            return null;
        }
    }
    
    /**
     * Get multiple cache items at once
     * @param array $keys Array of cache keys
     * @return array Associative array of key => data
     */
    public function getMultiple(array $keys): array {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        
        return $results;
    }
    
    /**
     * Set multiple cache items at once
     * @param array $items Associative array of key => data
     * @param int|null $ttl Time-to-live in seconds (null uses default)
     * @param array $tags Array of tags for all items
     * @return bool True on success
     */
    public function setMultiple(array $items, ?int $ttl = null, array $tags = []): bool {
        $success = true;
        
        foreach ($items as $key => $data) {
            if (!$this->set($key, $data, $ttl, $tags)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Increment a numeric cache value
     * @param string $key Cache key
     * @param int $amount Amount to increment (default 1)
     * @return int|false New value or false on failure
     */
    public function increment(string $key, int $amount = 1) {
        return $this->adjustNumericValue($key, $amount);
    }
    
    /**
     * Decrement a numeric cache value
     * @param string $key Cache key
     * @param int $amount Amount to decrement (default 1)
     * @return int|false New value or false on failure
     */
    public function decrement(string $key, int $amount = 1) {
        return $this->adjustNumericValue($key, -$amount);
    }
    
    /**
     * Garbage collection - remove expired cache items
     * @return int Number of items removed
     */
    public function collectGarbage(): int {
        $count = 0;
        $files = glob($this->cacheDir . '*.json');
        
        foreach ($files as $file) {
            $key = basename($file, '.json');
            $lock = $this->acquireLock($key);
            
            try {
                $content = file_get_contents($file);
                if ($content === false) continue;
                
                $cacheData = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) continue;
                
                if (isset($cacheData['meta']['expires_at']) && 
                    $cacheData['meta']['expires_at'] < time()) {
                    if (@unlink($file)) {
                        $count++;
                    }
                }
            } finally {
                $this->releaseLock($lock);
            }
        }
        
        return $count;
    }
    
    private function adjustNumericValue(string $key, int $amount) {
        $this->validateKey($key);
        
        $cacheFile = $this->getCacheFilePath($key);
        $lock = $this->acquireLock($key);
        
        try {
            if (!file_exists($cacheFile)) {
                $newValue = $amount;
                $this->set($key, $newValue);
                $this->releaseLock($lock);
                return $newValue;
            }
            
            $content = file_get_contents($cacheFile);
            if ($content === false) {
                $this->releaseLock($lock);
                return false;
            }
            
            $cacheData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->releaseLock($lock);
                return false;
            }
            
            if (!is_numeric($cacheData['data'])) {
                $this->releaseLock($lock);
                return false;
            }
            
            $newValue = $cacheData['data'] + $amount;
            $this->set($key, $newValue);
            
            $this->releaseLock($lock);
            return $newValue;
        } catch (Exception $e) {
            $this->releaseLock($lock);
            return false;
        }
    }
    
    private function getCacheFilePath(string $key): string {
        $this->validateKey($key);
        return $this->cacheDir . $key . '.json';
    }
    
    private function validateKey(string $key): void {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $key)) {
            throw new InvalidArgumentException('Invalid cache key');
        }
    }
    
    private function acquireLock(string $key) {
        $lockFile = $this->getCacheFilePath($key) . '.lock';
        $startTime = microtime(true);
        
        while (true) {
            if (@mkdir($lockFile, 0755)) {
                return $lockFile;
            }
            
            if (microtime(true) - $startTime > $this->fileLockTimeout) {
                throw new RuntimeException('Failed to acquire lock for key: ' . $key);
            }
            
            usleep(100000); // 100ms
        }
    }
    
    private function releaseLock(string $lockFile): void {
        @rmdir($lockFile);
    }
    
    /**
     * Set garbage collection probability
     * @param int $probability 1 in X chance to run GC (higher = less frequent)
     */
    public function setGcProbability(int $probability): void {
        $this->gcProbability = max(1, $probability);
    }
}