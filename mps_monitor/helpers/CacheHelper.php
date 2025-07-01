<?php // mps_monitor/helpers/CacheHelper.php
declare(strict_types=1);

/**
 * Robust Caching Helper for PHP CMS
 * Handles multiple API endpoints with flexible TTL and automatic cleanup.
 * This helper contributes to the API's statelessness by allowing responses
 * to be cached and served without re-executing the original API logic,
 * making the overall system more deterministic and efficient.
 */
class CacheHelper
{
    private string $cacheDir;
    private int $defaultTtl;
    private int $maxFileSize;
    private bool $enableCompression;

    /**
     * Constructor for the CacheHelper.
     * Initializes the cache directory, default TTL, max file size, and compression setting.
     * Creates the cache directory if it doesn't exist and adds a .htaccess file for protection.
     *
     * @param string $cacheDir Directory to store cache files. Defaults to './cache'.
     * @param int $defaultTtl Default Time-To-Live in seconds (e.g., 3600 for 1 hour).
     * @param int $maxFileSize Maximum cache file size in bytes (e.g., 5242880 for 5MB).
     * @param bool $enableCompression Whether to compress cache files using gzcompress/gzuncompress.
     */
    public function __construct(
        string $cacheDir = './cache',
        int $defaultTtl = 3600,
        int $maxFileSize = 5242880, // 5MB
        bool $enableCompression = true
    ) {
        $this->cacheDir = rtrim($cacheDir, '/\\'); // Ensure no trailing slash
        $this->defaultTtl = $defaultTtl;
        $this->maxFileSize = $maxFileSize;
        $this->enableCompression = $enableCompression;

        $this->initCacheDir();
    }

    /**
     * Initializes the cache directory, creating it if it doesn't exist.
     * Also places a .htaccess file to protect cache files from direct web access.
     */
    private function initCacheDir(): void
    {
        if (empty($this->cacheDir)) { // If cacheDir is empty, it means caching is effectively disabled.
            return;
        }

        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                custom_log("Failed to create cache directory: " . $this->cacheDir, 'ERROR');
                // Fallback: Disable caching if directory cannot be created
                $this->cacheDir = '';
                return;
            }
            custom_log("Cache directory created: " . $this->cacheDir, 'INFO');
        }

        // Add .htaccess to deny direct access to .cache and .tmp files
        $htaccessPath = $this->cacheDir . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = <<<EOT
# Deny direct access to cache files
<FilesMatch "\.(cache|tmp)$">
    Order allow,deny
    Deny from all
</FilesMatch>
EOT;
            // Attempt to write .htaccess. Log a warning if it fails.
            if (file_put_contents($htaccessPath, $htaccessContent) === false) {
                custom_log("Failed to create .htaccess in cache directory: " . $htaccessPath, 'WARNING');
            } else {
                custom_log(".htaccess created in cache directory.", 'DEBUG');
            }
        }
    }

    /**
     * Generates a safe filename for the cache entry based on the key.
     * Uses MD5 hash to ensure a fixed-length, safe filename.
     *
     * @param string $key The cache key.
     * @return string The full path to the cache file.
     */
    private function getCacheFilePath(string $key): string
    {
        if (empty($this->cacheDir)) {
            return ''; // Caching disabled if cacheDir is not set or failed to initialize
        }
        $filename = md5($key) . '.cache';
        return $this->cacheDir . '/' . $filename;
    }

    /**
     * Stores data in the cache.
     * Serializes and optionally compresses the data before writing to file.
     *
     * @param string $key Unique cache key.
     * @param mixed $data Data to cache.
     * @param int|null $ttl TTL in seconds. If null, uses defaultTtl.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, $data, ?int $ttl = null): bool
    {
        if (empty($this->cacheDir)) {
            return false; // Caching disabled
        }

        $filePath = $this->getCacheFilePath($key);
        if (empty($filePath)) {
            return false;
        }

        $expires = time() + ($ttl ?? $this->defaultTtl);
        $cacheData = [
            'expires' => $expires,
            'data'    => $data,
        ];

        // Serialize the data using PHP's serialize function
        $serializedData = serialize($cacheData);

        // Compress data if enabled
        if ($this->enableCompression) {
            $serializedData = gzcompress($serializedData, 9); // Compression level 9 (best)
            if ($serializedData === false) {
                custom_log("Failed to compress cache data for key: " . $key, 'ERROR');
                return false;
            }
        }

        // Check if the compressed data exceeds the maximum allowed file size
        if (strlen($serializedData) > $this->maxFileSize) {
            custom_log("Cache data for key '" . $key . "' exceeds max file size. Not caching.", 'WARNING');
            return false;
        }

        // Write to file using exclusive lock to prevent race conditions during write
        if (file_put_contents($filePath, $serializedData, LOCK_EX) === false) {
            custom_log("Failed to write cache file: " . $filePath, 'ERROR');
            return false;
        }
        custom_log("Cached data for key: " . $key . " (Expires in " . ($ttl ?? $this->defaultTtl) . "s)", 'DEBUG');
        return true;
    }

    /**
     * Retrieves data from the cache.
     * Decompresses and unserializes the data. Handles expired or corrupted cache files.
     *
     * @param string $key Cache key.
     * @param mixed $default Default value to return if cache miss.
     * @return mixed Cached data or default value.
     */
    public function get(string $key, $default = null)
    {
        if (empty($this->cacheDir)) {
            return $default; // Caching disabled
        }

        $filePath = $this->getCacheFilePath($key);
        // Check if file exists and is readable
        if (empty($filePath) || !file_exists($filePath) || !is_readable($filePath)) {
            return $default;
        }

        // Read file content. Use a shared lock to allow multiple reads.
        // Limit read length to maxFileSize + a buffer to detect oversized files.
        $serializedData = file_get_contents($filePath, false, null, 0, $this->maxFileSize + 1024);
        if ($serializedData === false) {
            custom_log("Failed to read cache file: " . $filePath, 'ERROR');
            return $default;
        }

        // Decompress data if compression was enabled during storage
        if ($this->enableCompression) {
            $decompressedData = gzuncompress($serializedData);
            if ($decompressedData === false) {
                custom_log("Failed to decompress cache data for key: " . $key . ". Deleting corrupted file.", 'ERROR');
                @unlink($filePath); // Attempt to delete corrupted file
                return $default;
            }
            $serializedData = $decompressedData;
        }

        // Unserialize the data. Use @ to suppress warnings from corrupted data.
        $cacheData = @unserialize($serializedData);

        // Validate unserialized data structure
        if ($cacheData === false || !isset($cacheData['expires'], $cacheData['data'])) {
            custom_log("Corrupted cache data for key: " . $key . ". Deleting corrupted file.", 'ERROR');
            @unlink($filePath); // Attempt to delete corrupted file
            return $default;
        }

        // Check if cache entry has expired
        if ($cacheData['expires'] < time()) {
            custom_log("Cache expired for key: " . $key . ". Deleting expired file.", 'DEBUG');
            @unlink($filePath); // Attempt to delete expired file
            return $default;
        }

        custom_log("Retrieved data from cache for key: " . $key, 'DEBUG');
        return $cacheData['data'];
    }

    /**
     * Deletes a specific cache entry.
     *
     * @param string $key Cache key to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(string $key): bool
    {
        if (empty($this->cacheDir)) {
            return false; // Caching disabled
        }

        $filePath = $this->getCacheFilePath($key);
        if (empty($filePath) || !file_exists($filePath)) {
            return true; // Already gone or never existed
        }

        if (unlink($filePath)) {
            custom_log("Deleted cache file for key: " . $key, 'INFO');
            return true;
        } else {
            custom_log("Failed to delete cache file for key: " . $key . " at " . $filePath, 'ERROR');
            return false;
        }
    }

    /**
     * Clears all cache entries from the cache directory.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool
    {
        if (empty($this->cacheDir) || !is_dir($this->cacheDir)) {
            return true; // Nothing to clear or caching disabled
        }

        $success = true;
        // Use glob to find all files ending with .cache in the cache directory
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file) && !unlink($file)) {
                custom_log("Failed to clear cache file: " . $file, 'ERROR');
                $success = false;
            }
        }
        if ($success) {
            custom_log("Cache cleared successfully.", 'INFO');
        }
        return $success;
    }

    /**
     * Performs garbage collection on expired cache files.
     * This method can be called periodically (e.g., via a cron job) or on demand
     * to clean up expired files that haven't been accessed and thus not deleted by get().
     */
    public function gc(): void
    {
        if (empty($this->cacheDir) || !is_dir($this->cacheDir)) {
            return;
        }

        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                $serializedData = file_get_contents($file);
                if ($serializedData === false) {
                    custom_log("GC: Failed to read file " . $file, 'ERROR');
                    continue;
                }

                if ($this->enableCompression) {
                    $decompressedData = gzuncompress($serializedData);
                    if ($decompressedData === false) {
                        custom_log("GC: Failed to decompress " . $file . ". Deleting corrupted file.", 'ERROR');
                        @unlink($file);
                        continue;
                    }
                    $serializedData = $decompressedData;
                }

                $cacheData = @unserialize($serializedData);
                // Check for corrupted data or missing 'expires' key
                if ($cacheData === false || !isset($cacheData['expires'])) {
                    custom_log("GC: Corrupted cache data in " . $file . ". Deleting file.", 'ERROR');
                    @unlink($file);
                    continue;
                }

                // If the cache entry has expired, delete the file
                if ($cacheData['expires'] < time()) {
                    custom_log("GC: Expired cache file deleted: " . $file, 'DEBUG');
                    @unlink($file);
                }
            }
        }
        custom_log("Cache garbage collection completed.", 'DEBUG');
    }
}

// Global helper function for convenience (uses a singleton pattern for CacheHelper)
// This ensures only one instance of CacheHelper is created and reused throughout the request.
function getCache(): CacheHelper
{
    static $cache = null;
    if ($cache === null) {
        // Assuming cache directory is ../../cache from helpers folder
        // This path must be writable by the web server.
        $cacheDir = __DIR__ . '/../../cache';
        // Use DEFAULT_CACHE_TTL defined in mps_config.php
        $cache = new CacheHelper($cacheDir, DEFAULT_CACHE_TTL);
    }
    return $cache;
}

/**
 * A quick wrapper for CacheHelper::set().
 * Stores data in the cache using the default CacheHelper instance.
 *
 * @param string $key Unique cache key.
 * @param mixed $data Data to cache.
 * @param int $ttl TTL in seconds. Defaults to DEFAULT_CACHE_TTL.
 * @return bool True on success, false on failure.
 */
function cache_set(string $key, $data, int $ttl = DEFAULT_CACHE_TTL): bool
{
    return getCache()->set($key, $data, $ttl);
}

/**
 * A quick wrapper for CacheHelper::get().
 * Retrieves data from the cache using the default CacheHelper instance.
 *
 * @param string $key Cache key.
 * @param mixed $default Default value to return if cache miss. Defaults to null.
 * @return mixed Cached data or default value.
 */
function cache_get(string $key, $default = null)
{
    return getCache()->get($key, $default);
}

/**
 * A quick wrapper for the cache-or-execute pattern.
 * Attempts to retrieve data from cache. If not found or expired, it executes the provided callback function,
 * caches its result, and then returns the result.
 * This is useful for "remembering" expensive operations.
 *
 * @param string $key Cache key.
 * @param callable $callback Function to execute if cache miss.
 * @param int $ttl TTL in seconds. Defaults to DEFAULT_CACHE_TTL.
 * @return mixed The cached data or the result of the callback.
 */
function cache_remember(string $key, callable $callback, int $ttl = DEFAULT_CACHE_TTL)
{
    $cached = cache_get($key);
    if ($cached !== null) {
        return $cached;
    }

    $result = $callback();
    cache_set($key, $result, $ttl);
    return $result;
}
?>
