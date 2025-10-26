<?php
/**
 * MySQL-based Cache System
 *
 * Persistent caching using MySQL database for better performance and reliability
 */

require_once __DIR__ . '/Database.php';

class MySQLCache {
    private $db;
    private $tableName;
    private $defaultTTL = 300; // 5 minutes default

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tableName = $this->db->getPrefix() . 'cache';
        $this->initializeTable();
    }

    /**
     * Create cache table if it doesn't exist
     */
    private function initializeTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cache_key VARCHAR(255) UNIQUE NOT NULL,
            cache_value LONGTEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            hit_count INT DEFAULT 0,
            INDEX idx_cache_key (cache_key),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->db->execute($sql);
        } catch (Exception $e) {
            error_log('Failed to create cache table: ' . $e->getMessage());
        }
    }

    /**
     * Get value from cache
     */
    public function get($key) {
        // Clean expired entries first
        $this->cleanExpired();

        $sql = "SELECT cache_value, hit_count FROM {$this->tableName}
                WHERE cache_key = ? AND expires_at > NOW()";

        try {
            $result = $this->db->query($sql, [$key]);

            if (empty($result)) {
                return null;
            }

            // Increment hit count
            $this->incrementHitCount($key);

            return json_decode($result[0]['cache_value'], true);
        } catch (Exception $e) {
            error_log('Cache get failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set value in cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
        $jsonValue = json_encode($value);

        $sql = "INSERT INTO {$this->tableName} (cache_key, cache_value, expires_at)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    cache_value = VALUES(cache_value),
                    expires_at = VALUES(expires_at),
                    hit_count = 0";

        try {
            $this->db->execute($sql, [$key, $jsonValue, $expiresAt]);
            return true;
        } catch (Exception $e) {
            error_log('Cache set failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete value from cache
     */
    public function delete($key) {
        $sql = "DELETE FROM {$this->tableName} WHERE cache_key = ?";

        try {
            return $this->db->execute($sql, [$key]) > 0;
        } catch (Exception $e) {
            error_log('Cache delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if key exists in cache
     */
    public function has($key) {
        $sql = "SELECT 1 FROM {$this->tableName}
                WHERE cache_key = ? AND expires_at > NOW()";

        try {
            $result = $this->db->query($sql, [$key]);
            return !empty($result);
        } catch (Exception $e) {
            error_log('Cache has failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cache entries
     */
    public function clear() {
        $sql = "TRUNCATE TABLE {$this->tableName}";

        try {
            $this->db->execute($sql);
            return true;
        } catch (Exception $e) {
            error_log('Cache clear failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpired() {
        $sql = "DELETE FROM {$this->tableName} WHERE expires_at <= NOW()";

        try {
            return $this->db->execute($sql);
        } catch (Exception $e) {
            error_log('Cache clean failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats() {
        $sql = "SELECT
                    COUNT(*) as total_entries,
                    SUM(CASE WHEN expires_at > NOW() THEN 1 ELSE 0 END) as active_entries,
                    SUM(CASE WHEN expires_at <= NOW() THEN 1 ELSE 0 END) as expired_entries,
                    SUM(hit_count) as total_hits,
                    AVG(hit_count) as avg_hit_count,
                    SUM(LENGTH(cache_value)) as total_size_bytes
                FROM {$this->tableName}";

        try {
            $result = $this->db->query($sql);
            return $result[0] ?? [
                'total_entries' => 0,
                'active_entries' => 0,
                'expired_entries' => 0,
                'total_hits' => 0,
                'avg_hit_count' => 0,
                'total_size_bytes' => 0
            ];
        } catch (Exception $e) {
            error_log('Cache stats failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all cache entries (for debugging)
     */
    public function getAllEntries() {
        $sql = "SELECT cache_key,
                       LENGTH(cache_value) as size_bytes,
                       expires_at,
                       created_at,
                       updated_at,
                       hit_count,
                       CASE WHEN expires_at > NOW() THEN 'active' ELSE 'expired' END as status
                FROM {$this->tableName}
                ORDER BY updated_at DESC";

        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Cache get all failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Increment hit count for a cache key
     */
    private function incrementHitCount($key) {
        $sql = "UPDATE {$this->tableName}
                SET hit_count = hit_count + 1
                WHERE cache_key = ?";

        try {
            $this->db->execute($sql, [$key]);
        } catch (Exception $e) {
            error_log('Hit count increment failed: ' . $e->getMessage());
        }
    }

    /**
     * Warm cache with specific endpoints
     */
    public function warm($endpoints = []) {
        $warmed = 0;
        foreach ($endpoints as $endpoint) {
            // This would be called from the API layer
            // For now, just track the attempt
            $warmed++;
        }
        return $warmed;
    }
}
