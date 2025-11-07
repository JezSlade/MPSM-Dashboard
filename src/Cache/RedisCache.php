<?php
/**
 * Redis Cache Driver
 * Stores cache in Redis (if available)
 *
 * Benefits:
 * - Extremely fast (in-memory)
 * - Supports clustering
 * - Automatic expiration
 * - Perfect for high-traffic applications
 *
 * Requirements:
 * - Redis server installed
 * - PHP Redis extension
 */

class RedisCache implements CacheInterface
{
    private Redis $redis;
    private string $prefix;

    /**
     * @param array $config Redis configuration
     *   - host: Redis host (default localhost)
     *   - port: Redis port (default 6379)
     *   - password: Redis password (optional)
     *   - database: Redis database number (default 0)
     *   - prefix: Key prefix (default mpsm:)
     */
    public function __construct(array $config = [])
    {
        if (!extension_loaded('redis')) {
            throw new RuntimeException('Redis extension not loaded');
        }

        $this->redis = new Redis();

        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 2.5;

        if (!$this->redis->connect($host, $port, $timeout)) {
            throw new RuntimeException("Failed to connect to Redis at {$host}:{$port}");
        }

        // Authenticate if password provided
        if (!empty($config['password'])) {
            if (!$this->redis->auth($config['password'])) {
                throw new RuntimeException('Redis authentication failed');
            }
        }

        // Select database
        $database = $config['database'] ?? 0;
        $this->redis->select($database);

        // Set key prefix
        $this->prefix = $config['prefix'] ?? 'mpsm:';
    }

    /**
     * Get cache value
     */
    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($this->prefix . $key);

        if ($value === false) {
            return $default;
        }

        return unserialize($value);
    }

    /**
     * Set cache value
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? 3600;
        $serialized = serialize($value);

        return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    /**
     * Delete cache key
     */
    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    /**
     * Clear all cache (with prefix)
     */
    public function clear(): bool
    {
        // Get all keys with prefix
        $keys = $this->redis->keys($this->prefix . '*');

        if (empty($keys)) {
            return true;
        }

        // Remove prefix from keys before deletion
        $keysWithoutPrefix = array_map(function($key) {
            return substr($key, strlen($this->prefix));
        }, $keys);

        return $this->deleteMultiple($keysWithoutPrefix);
    }

    /**
     * Get multiple values
     */
    public function getMultiple(array $keys, $default = null): array
    {
        if (empty($keys)) {
            return [];
        }

        // Add prefix to keys
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);

        $values = $this->redis->mget($prefixedKeys);

        $results = [];
        foreach ($keys as $index => $key) {
            $value = $values[$index];

            if ($value === false) {
                $results[$key] = $default;
            } else {
                $results[$key] = unserialize($value);
            }
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
        $pipeline = $this->redis->multi(Redis::PIPELINE);

        foreach ($values as $key => $value) {
            $pipeline->setex($this->prefix . $key, $ttl, serialize($value));
        }

        $results = $pipeline->exec();

        // Check if all operations succeeded
        return !in_array(false, $results, true);
    }

    /**
     * Delete multiple keys
     */
    public function deleteMultiple(array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        // Add prefix to keys
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);

        return $this->redis->del(...$prefixedKeys) > 0;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $info = $this->redis->info();

        $keys = $this->redis->keys($this->prefix . '*');
        $totalEntries = count($keys);

        $memory = $info['used_memory_human'] ?? 'N/A';

        return [
            'total_entries' => $totalEntries,
            'valid_entries' => $totalEntries, // Redis auto-expires
            'expired_entries' => 0,
            'memory_used' => $memory,
            'connected_clients' => $info['connected_clients'] ?? 0,
            'uptime_seconds' => $info['uptime_in_seconds'] ?? 0,
        ];
    }

    /**
     * Increment value
     */
    public function increment(string $key, int $value = 1): int
    {
        return $this->redis->incrBy($this->prefix . $key, $value);
    }

    /**
     * Decrement value
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->redis->decrBy($this->prefix . $key, $value);
    }
}
