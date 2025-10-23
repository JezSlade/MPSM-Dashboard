<?php
/**
 * SQLite Cache Layer for MPS Monitor CMS
 * Provides fast caching of API responses to improve performance
 */

class MPSCache {
    private $db;
    private $cacheDuration = 300; // 5 minutes default

    public function __construct($dbPath = null) {
        if ($dbPath === null) {
            $dbPath = __DIR__ . '/cache/mps_cache.db';
        }

        // Ensure cache directory exists
        $cacheDir = dirname($dbPath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Open SQLite database
        $this->db = new SQLite3($dbPath);

        // Create cache table if not exists
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS api_cache (
                cache_key TEXT PRIMARY KEY,
                action TEXT NOT NULL,
                params TEXT NOT NULL,
                response TEXT NOT NULL,
                created_at INTEGER NOT NULL,
                expires_at INTEGER NOT NULL
            )
        ');

        // Create index on expiration
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_expires ON api_cache(expires_at)');

        // Clean up expired entries on init
        $this->cleanup();
    }

    /**
     * Get cached response
     */
    public function get($action, $params = []) {
        $cacheKey = $this->getCacheKey($action, $params);
        $now = time();

        $stmt = $this->db->prepare('
            SELECT response FROM api_cache
            WHERE cache_key = :key AND expires_at > :now
        ');
        $stmt->bindValue(':key', $cacheKey, SQLITE3_TEXT);
        $stmt->bindValue(':now', $now, SQLITE3_INTEGER);

        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row) {
            return json_decode($row['response'], true);
        }

        return null;
    }

    /**
     * Store response in cache
     */
    public function set($action, $params = [], $response, $ttl = null) {
        $cacheKey = $this->getCacheKey($action, $params);
        $now = time();
        $ttl = $ttl ?? $this->cacheDuration;
        $expiresAt = $now + $ttl;

        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO api_cache
            (cache_key, action, params, response, created_at, expires_at)
            VALUES (:key, :action, :params, :response, :created, :expires)
        ');

        $stmt->bindValue(':key', $cacheKey, SQLITE3_TEXT);
        $stmt->bindValue(':action', $action, SQLITE3_TEXT);
        $stmt->bindValue(':params', json_encode($params), SQLITE3_TEXT);
        $stmt->bindValue(':response', json_encode($response), SQLITE3_TEXT);
        $stmt->bindValue(':created', $now, SQLITE3_INTEGER);
        $stmt->bindValue(':expires', $expiresAt, SQLITE3_INTEGER);

        return $stmt->execute();
    }

    /**
     * Clear cache for specific action
     */
    public function clear($action = null) {
        if ($action === null) {
            // Clear all cache
            $this->db->exec('DELETE FROM api_cache');
        } else {
            // Clear specific action
            $stmt = $this->db->prepare('DELETE FROM api_cache WHERE action = :action');
            $stmt->bindValue(':action', $action, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    /**
     * Remove expired entries
     */
    public function cleanup() {
        $now = time();
        $stmt = $this->db->prepare('DELETE FROM api_cache WHERE expires_at < :now');
        $stmt->bindValue(':now', $now, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * Get cache statistics
     */
    public function getStats() {
        $total = $this->db->querySingle('SELECT COUNT(*) FROM api_cache');
        $expired = $this->db->querySingle('SELECT COUNT(*) FROM api_cache WHERE expires_at < ' . time());
        $size = $this->db->querySingle('SELECT page_count * page_size FROM pragma_page_count(), pragma_page_size()');

        return [
            'total_entries' => $total,
            'expired_entries' => $expired,
            'active_entries' => $total - $expired,
            'database_size' => $size,
            'database_size_human' => $this->formatBytes($size)
        ];
    }

    /**
     * Generate cache key from action and params
     */
    private function getCacheKey($action, $params) {
        ksort($params); // Consistent ordering
        return md5($action . ':' . json_encode($params));
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function __destruct() {
        $this->db->close();
    }
}
