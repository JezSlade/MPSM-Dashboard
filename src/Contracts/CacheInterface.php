<?php
/**
 * Cache Interface
 * Contract for cache drivers
 *
 * Supports multiple cache backends: database, file, Redis, etc.
 */

interface CacheInterface
{
    /**
     * Retrieve an item from the cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if key not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null);

    /**
     * Store an item in the cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (null = use default)
     * @return bool Success status
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * Check if an item exists in cache
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove an item from cache
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool;

    /**
     * Clear all items from cache
     *
     * @return bool Success status
     */
    public function clear(): bool;

    /**
     * Get multiple items from cache
     *
     * @param array $keys Array of cache keys
     * @param mixed $default Default value for missing keys
     * @return array Key-value pairs
     */
    public function getMultiple(array $keys, $default = null): array;

    /**
     * Store multiple items in cache
     *
     * @param array $values Key-value pairs to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool Success status
     */
    public function setMultiple(array $values, ?int $ttl = null): bool;

    /**
     * Remove multiple items from cache
     *
     * @param array $keys Array of cache keys
     * @return bool Success status
     */
    public function deleteMultiple(array $keys): bool;
}
