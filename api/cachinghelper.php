<?php

/**
 * Robust Caching Helper for PHP CMS
 * Handles multiple API endpoints with flexible TTL and automatic cleanup
 */
class CacheHelper 
{
    private $cacheDir;
    private $defaultTtl;
    private $maxFileSize;
    private $enableCompression;
    
    /**
     * Constructor
     * 
     * @param string $cacheDir Directory to store cache files
     * @param int $defaultTtl Default TTL in seconds (3600 = 1 hour)
     * @param int $maxFileSize Maximum cache file size in bytes (5MB default)
     * @param bool $enableCompression Whether to compress cache files
     */
    public function __construct($cacheDir = './cache', $defaultTtl = 3600, $maxFileSize = 5242880, $enableCompression = true) 
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->defaultTtl = $defaultTtl;
        $this->maxFileSize = $maxFileSize;
        $this->enableCompression = $enableCompression;
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                throw new Exception("Failed to create cache directory: {$this->cacheDir}");
            }
        }
        
        // Create .htaccess to protect cache directory
        $this->createHtaccess();
    }
    
    /**
     * Store data in cache
     * 
     * @param string $key Unique cache key (endpoint identifier)
     * @param mixed $data Data to cache
     * @param int|null $ttl Time to live in seconds (null uses default)
     * @param array $tags Optional tags for cache invalidation
     * @return bool Success status
     */
    public function set($key, $data, $ttl = null, $tags = []) 
    {
        try {
            $ttl = $ttl ?? $this->defaultTtl;
            $filename = $this->getFilename($key);
            
            $cacheData = [
                'key' => $key,
                'data' => $data,
                'created_at' => time(),
                'expires_at' => time() + $ttl,
                'ttl' => $ttl,
                'tags' => $tags,
                'size' => strlen(serialize($data)),
                'checksum' => md5(serialize($data))
            ];
            
            $serialized = serialize($cacheData);
            
            // Check file size limit
            if (strlen($serialized) > $this->maxFileSize) {
                error_log("Cache data too large for key: {$key}");
                return false;
            }
            
            // Compress if enabled
            if ($this->enableCompression) {
                $serialized = gzcompress($serialized, 6);
            }
            
            // Atomic write using temporary file
            $tempFile = $filename . '.tmp.' . uniqid();
            if (file_put_contents($tempFile, $serialized, LOCK_EX) !== false) {
                if (rename($tempFile, $filename)) {
                    // Update cache index
                    $this->updateIndex($key, $cacheData);
                    return true;
                }
            }
            
            // Cleanup temp file if write failed
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Cache set error for key {$key}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve data from cache
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if cache miss
     * @return mixed Cached data or default value
     */
    public function get($key, $default = null) 
    {
        try {
            $filename = $this->getFilename($key);
            
            if (!file_exists($filename)) {
                return $default;
            }
            
            $content = file_get_contents($filename);
            if ($content === false) {
                return $default;
            }
            
            // Decompress if enabled
            if ($this->enableCompression) {
                $content = gzuncompress($content);
                if ($content === false) {
                    error_log("Failed to decompress cache file: {$filename}");
                    return $default;
                }
            }
            
            $cacheData = unserialize($content);
            if ($cacheData === false) {
                error_log("Failed to unserialize cache file: {$filename}");
                return $default;
            }
            
            // Check if expired
            if (time() > $cacheData['expires_at']) {
                $this->delete($key);
                return $default;
            }
            
            // Verify data integrity
            if (md5(serialize($cacheData['data'])) !== $cacheData['checksum']) {
                error_log("Cache data corrupted for key: {$key}");
                $this->delete($key);
                return $default;
            }
            
            return $cacheData['data'];
            
        } catch (Exception $e) {
            error_log("Cache get error for key {$key}: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Check if cache key exists and is valid
     * 
     * @param string $key Cache key
     * @return bool
     */
    public function exists($key) 
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        try {
            $content = file_get_contents($filename);
            if ($this->enableCompression) {
                $content = gzuncompress($content);
            }
            
            $cacheData = unserialize($content);
            return time() <= $cacheData['expires_at'];
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete cache entry
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key) 
    {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            $result = unlink($filename);
            $this->removeFromIndex($key);
            return $result;
        }
        
        return true;
    }
    
    /**
     * Get cache statistics for a key
     * 
     * @param string $key Cache key
     * @return array|null Cache metadata
     */
    public function getStats($key) 
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        try {
            $content = file_get_contents($filename);
            if ($this->enableCompression) {
                $content = gzuncompress($content);
            }
            
            $cacheData = unserialize($content);
            
            return [
                'key' => $cacheData['key'],
                'created_at' => $cacheData['created_at'],
                'expires_at' => $cacheData['expires_at'],
                'ttl' => $cacheData['ttl'],
                'age' => time() - $cacheData['created_at'],
                'remaining_ttl' => $cacheData['expires_at'] - time(),
                'size' => $cacheData['size'],
                'tags' => $cacheData['tags'],
                'is_expired' => time() > $cacheData['expires_at']
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Clear all cache entries
     * 
     * @return bool Success status
     */
    public function clear() 
    {
        try {
            $files = glob($this->cacheDir . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
            
            // Clear index
            $indexFile = $this->cacheDir . '/cache_index.json';
            if (file_exists($indexFile)) {
                unlink($indexFile);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Cache clear error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired cache entries
     * 
     * @return int Number of cleaned entries
     */
    public function cleanup() 
    {
        $cleaned = 0;
        
        try {
            $files = glob($this->cacheDir . '/*.cache');
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($this->enableCompression) {
                    $content = gzuncompress($content);
                }
                
                $cacheData = unserialize($content);
                
                if (time() > $cacheData['expires_at']) {
                    unlink($file);
                    $this->removeFromIndex($cacheData['key']);
                    $cleaned++;
                }
            }
            
        } catch (Exception $e) {
            error_log("Cache cleanup error: " . $e->getMessage());
        }
        
        return $cleaned;
    }
    
    /**
     * Invalidate cache entries by tags
     * 
     * @param array $tags Tags to invalidate
     * @return int Number of invalidated entries
     */
    public function invalidateByTags($tags) 
    {
        $invalidated = 0;
        
        try {
            $files = glob($this->cacheDir . '/*.cache');
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($this->enableCompression) {
                    $content = gzuncompress($content);
                }
                
                $cacheData = unserialize($content);
                
                // Check if any of the tags match
                if (array_intersect($tags, $cacheData['tags'])) {
                    unlink($file);
                    $this->removeFromIndex($cacheData['key']);
                    $invalidated++;
                }
            }
            
        } catch (Exception $e) {
            error_log("Cache invalidation error: " . $e->getMessage());
        }
        
        return $invalidated;
    }
    
    /**
     * Get all cache keys
     * 
     * @return array Array of cache keys
     */
    public function getAllKeys() 
    {
        $keys = [];
        
        try {
            $files = glob($this->cacheDir . '/*.cache');
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if ($this->enableCompression) {
                    $content = gzuncompress($content);
                }
                
                $cacheData = unserialize($content);
                if (time() <= $cacheData['expires_at']) {
                    $keys[] = $cacheData['key'];
                }
            }
            
        } catch (Exception $e) {
            error_log("Get all keys error: " . $e->getMessage());
        }
        
        return $keys;
    }
    
    /**
     * Get cache summary statistics
     * 
     * @return array Cache statistics
     */
    public function getSummary() 
    {
        $total = 0;
        $expired = 0;
        $totalSize = 0;
        
        try {
            $files = glob($this->cacheDir . '/*.cache');
            
            foreach ($files as $file) {
                $total++;
                $totalSize += filesize($file);
                
                $content = file_get_contents($file);
                if ($this->enableCompression) {
                    $content = gzuncompress($content);
                }
                
                $cacheData = unserialize($content);
                if (time() > $cacheData['expires_at']) {
                    $expired++;
                }
            }
            
        } catch (Exception $e) {
            error_log("Get summary error: " . $e->getMessage());
        }
        
        return [
            'total_entries' => $total,
            'valid_entries' => $total - $expired,
            'expired_entries' => $expired,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'cache_directory' => $this->cacheDir
        ];
    }
    
    /**
     * Generate cache filename from key
     * 
     * @param string $key Cache key
     * @return string Full path to cache file
     */
    private function getFilename($key) 
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        $hash = md5($key);
        return $this->cacheDir . '/' . $safeKey . '_' . $hash . '.cache';
    }
    
    /**
     * Create .htaccess file to protect cache directory
     */
    private function createHtaccess() 
    {
        $htaccessFile = $this->cacheDir . '/.htaccess';
        
        if (!file_exists($htaccessFile)) {
            $content = "# Deny access to cache files\n";
            $content .= "Order deny,allow\n";
            $content .= "Deny from all\n";
            $content .= "<Files ~ \"\\.(cache|tmp)$\">\n";
            $content .= "    Deny from all\n";
            $content .= "</Files>\n";
            
            file_put_contents($htaccessFile, $content);
        }
    }
    
    /**
     * Update cache index for faster lookups
     * 
     * @param string $key Cache key
     * @param array $cacheData Cache metadata
     */
    private function updateIndex($key, $cacheData) 
    {
        $indexFile = $this->cacheDir . '/cache_index.json';
        $index = [];
        
        if (file_exists($indexFile)) {
            $index = json_decode(file_get_contents($indexFile), true) ?: [];
        }
        
        $index[$key] = [
            'expires_at' => $cacheData['expires_at'],
            'tags' => $cacheData['tags'],
            'size' => $cacheData['size']
        ];
        
        file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT));
    }
    
    /**
     * Remove entry from cache index
     * 
     * @param string $key Cache key
     */
    private function removeFromIndex($key) 
    {
        $indexFile = $this->cacheDir . '/cache_index.json';
        
        if (file_exists($indexFile)) {
            $index = json_decode(file_get_contents($indexFile), true) ?: [];
            unset($index[$key]);
            file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT));
        }
    }
}

// Usage Examples and Convenience Functions

/**
 * Get global cache instance (singleton pattern)
 * 
 * @param string $cacheDir Cache directory
 * @return CacheHelper
 */
function getCache($cacheDir = './cache') 
{
    static $instance = null;
    
    if ($instance === null) {
        $instance = new CacheHelper($cacheDir);
    }
    
    return $instance;
}

/**
 * Quick cache set function
 * 
 * @param string $key Cache key
 * @param mixed $data Data to cache
 * @param int $ttl TTL in seconds
 * @return bool
 */
function cache_set($key, $data, $ttl = 3600) 
{
    return getCache()->set($key, $data, $ttl);
}

/**
 * Quick cache get function
 * 
 * @param string $key Cache key
 * @param mixed $default Default value
 * @return mixed
 */
function cache_get($key, $default = null) 
{
    return getCache()->get($key, $default);
}

/**
 * Cache or execute callback function
 * 
 * @param string $key Cache key
 * @param callable $callback Function to execute if cache miss
 * @param int $ttl TTL in seconds
 * @return mixed
 */
function cache_remember($key, $callback, $ttl = 3600) 
{
    $cache = getCache();
    
    if ($cache->exists($key)) {
        return $cache->get($key);
    }
    
    $data = $callback();
    $cache->set($key, $data, $ttl);
    
    return $data;
}

/*
USAGE EXAMPLES:

// Basic usage
$cache = new CacheHelper('./cache');

// Cache API response for 1 hour
$apiData = ['users' => [1, 2, 3], 'total' => 150];
$cache->set('api_users', $apiData, 3600);

// Retrieve cached data
$users = $cache->get('api_users', []);

// Cache with tags for bulk invalidation
$cache->set('dashboard_stats', $stats, 1800, ['dashboard', 'stats']);
$cache->set('user_metrics', $metrics, 1800, ['dashboard', 'users']);

// Invalidate all dashboard-related cache
$cache->invalidateByTags(['dashboard']);

// Using convenience functions
cache_set('api_endpoint_1', $data, 3600);
$cachedData = cache_get('api_endpoint_1');

// Cache with callback (cache-or-execute pattern)
$expensiveData = cache_remember('expensive_operation', function() {
    // Expensive API call or database query
    return fetchExpensiveData();
}, 7200);

// Cleanup expired entries (run via cron)
$cache->cleanup();

// Get cache statistics
$stats = $cache->getSummary();
print_r($stats);
*/