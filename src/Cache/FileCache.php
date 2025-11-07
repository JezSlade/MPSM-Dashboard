<?php
/**
 * File Cache Driver
 * Stores cache as files on disk
 *
 * Benefits:
 * - Fast reads/writes
 * - No database overhead
 * - Easy to clear (delete files)
 * - Good for small to medium data
 */

class FileCache implements CacheInterface
{
    private string $cachePath;

    public function __construct(string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? sys_get_temp_dir() . '/mpsm_cache';
        $this->ensureDirectoryExists();
    }

    /**
     * Get cache value
     */
    public function get(string $key, $default = null)
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return $default;
        }

        $data = unserialize($contents);

        // Check expiration
        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set cache value
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? 3600;
        $expiresAt = time() + $ttl;

        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time(),
        ];

        $filePath = $this->getFilePath($key);
        $serialized = serialize($data);

        return file_put_contents($filePath, $serialized, LOCK_EX) !== false;
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete cache key
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get multiple values
     */
    public function getMultiple(array $keys, $default = null): array
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /**
     * Set multiple values
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Delete multiple keys
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Clean up expired cache entries
     */
    public function cleanup(): int
    {
        $files = glob($this->cachePath . '/*');
        $count = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            $data = unserialize($contents);

            if ($data['expires_at'] !== null && time() > $data['expires_at']) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $files = glob($this->cachePath . '/*');
        $totalEntries = 0;
        $validEntries = 0;
        $expiredEntries = 0;
        $totalBytes = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $totalEntries++;
            $totalBytes += filesize($file);

            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            $data = unserialize($contents);

            if ($data['expires_at'] === null || time() <= $data['expires_at']) {
                $validEntries++;
            } else {
                $expiredEntries++;
            }
        }

        return [
            'total_entries' => $totalEntries,
            'valid_entries' => $validEntries,
            'expired_entries' => $expiredEntries,
            'total_size_bytes' => $totalBytes,
            'total_size_mb' => round($totalBytes / 1024 / 1024, 2),
            'cache_path' => $this->cachePath,
        ];
    }

    /**
     * Get file path for cache key
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
}
