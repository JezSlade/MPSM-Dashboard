<?php
/**
 * Database Cache Driver
 * Stores cache in mpsm_cache table
 *
 * Benefits:
 * - No external dependencies (Redis, Memcached)
 * - Persistent across server restarts
 * - Easy to debug and inspect
 * - Automatic expiration cleanup
 */

class DatabaseCache implements CacheInterface
{
    private PDO $pdo;
    private string $table = 'mpsm_cache';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    /**
     * Get cache value
     */
    public function get(string $key, $default = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT value, expires_at
            FROM {$this->table}
            WHERE cache_key = ?
            LIMIT 1
        ");
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if (!$row) {
            return $default;
        }

        // Check expiration
        if ($row['expires_at'] !== null && time() > strtotime($row['expires_at'])) {
            $this->delete($key);
            return $default;
        }

        return unserialize($row['value']);
    }

    /**
     * Set cache value
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? 3600; // Default 1 hour
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
        $serialized = serialize($value);

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (cache_key, value, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                value = VALUES(value),
                expires_at = VALUES(expires_at),
                created_at = NOW()
        ");

        return $stmt->execute([$key, $serialized, $expiresAt]);
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        $value = $this->get($key);
        return $value !== null;
    }

    /**
     * Delete cache key
     */
    public function delete(string $key): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table}
            WHERE cache_key = ?
        ");

        return $stmt->execute([$key]);
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        return $this->pdo->exec("TRUNCATE TABLE {$this->table}") !== false;
    }

    /**
     * Get multiple values
     */
    public function getMultiple(array $keys, $default = null): array
    {
        if (empty($keys)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->pdo->prepare("
            SELECT cache_key, value, expires_at
            FROM {$this->table}
            WHERE cache_key IN ({$placeholders})
        ");
        $stmt->execute($keys);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $default;
        }

        foreach ($rows as $row) {
            $key = $row['cache_key'];

            // Check expiration
            if ($row['expires_at'] !== null && time() > strtotime($row['expires_at'])) {
                $this->delete($key);
                continue;
            }

            $results[$key] = unserialize($row['value']);
        }

        return $results;
    }

    /**
     * Set multiple values
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        if (empty($values)) {
            return true;
        }

        $ttl = $ttl ?? 3600;
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (cache_key, value, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    value = VALUES(value),
                    expires_at = VALUES(expires_at),
                    created_at = NOW()
            ");

            foreach ($values as $key => $value) {
                $stmt->execute([$key, serialize($value), $expiresAt]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DatabaseCache::setMultiple error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple keys
     */
    public function deleteMultiple(array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table}
            WHERE cache_key IN ({$placeholders})
        ");

        return $stmt->execute($keys);
    }

    /**
     * Clean up expired cache entries
     */
    public function cleanup(): int
    {
        $stmt = $this->pdo->query("
            DELETE FROM {$this->table}
            WHERE expires_at IS NOT NULL
            AND expires_at < NOW()
        ");

        return $stmt->rowCount();
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) as total_entries,
                SUM(CASE WHEN expires_at IS NULL OR expires_at > NOW() THEN 1 ELSE 0 END) as valid_entries,
                SUM(CASE WHEN expires_at IS NOT NULL AND expires_at < NOW() THEN 1 ELSE 0 END) as expired_entries,
                SUM(LENGTH(value)) as total_bytes
            FROM {$this->table}
        ");

        $stats = $stmt->fetch();

        return [
            'total_entries' => (int) $stats['total_entries'],
            'valid_entries' => (int) $stats['valid_entries'],
            'expired_entries' => (int) $stats['expired_entries'],
            'total_size_bytes' => (int) $stats['total_bytes'],
            'total_size_mb' => round($stats['total_bytes'] / 1024 / 1024, 2),
        ];
    }

    /**
     * Ensure cache table exists
     */
    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                cache_key VARCHAR(255) PRIMARY KEY,
                value LONGTEXT NOT NULL,
                expires_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
