<?php
/**
 * Cache Integration for MPS API Engine
 *
 * Integrates MySQL-based caching into the API engine
 */

require_once __DIR__ . '/../cms/classes/MySQLCache.php';

class CacheIntegration {
    private static $cache = null;
    private static $enabled = true;
    private static $defaultTTL = 300; // 5 minutes

    /**
     * Initialize cache
     */
    public static function init() {
        if (self::$cache === null) {
            try {
                self::$cache = new MySQLCache();
            } catch (Exception $e) {
                error_log('Cache initialization failed: ' . $e->getMessage());
                self::$enabled = false;
            }
        }
        return self::$cache;
    }

    /**
     * Get cached response for an API call
     */
    public static function get($endpoint, $params = []) {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return null;
        }

        $cacheKey = self::generateKey($endpoint, $params);
        return self::$cache->get($cacheKey);
    }

    /**
     * Store API response in cache
     */
    public static function set($endpoint, $params, $response, $ttl = null) {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return false;
        }

        $cacheKey = self::generateKey($endpoint, $params);
        $ttl = $ttl ?? self::$defaultTTL;

        return self::$cache->set($cacheKey, $response, $ttl);
    }

    /**
     * Clear cache for specific endpoint
     */
    public static function clear($endpoint = null, $params = []) {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return false;
        }

        if ($endpoint === null) {
            return self::$cache->clear();
        }

        $cacheKey = self::generateKey($endpoint, $params);
        return self::$cache->delete($cacheKey);
    }

    /**
     * Get cache statistics
     */
    public static function getStats() {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return [
                'enabled' => false,
                'error' => 'Cache not available'
            ];
        }

        $stats = self::$cache->getStats();

        // Calculate hit rate
        $activeEntries = (int)($stats['active_entries'] ?? 0);
        $totalHits = (int)($stats['total_hits'] ?? 0);
        $hitRate = $activeEntries > 0 ? round(($totalHits / $activeEntries) * 100, 2) : 0;

        // Format size
        $sizeBytes = (int)($stats['total_size_bytes'] ?? 0);
        $sizeMB = round($sizeBytes / 1024 / 1024, 2);

        return [
            'enabled' => true,
            'total_entries' => (int)($stats['total_entries'] ?? 0),
            'active_entries' => $activeEntries,
            'expired_entries' => (int)($stats['expired_entries'] ?? 0),
            'total_hits' => $totalHits,
            'avg_hit_count' => round((float)($stats['avg_hit_count'] ?? 0), 2),
            'hit_rate' => $hitRate,
            'size_bytes' => $sizeBytes,
            'size_mb' => $sizeMB
        ];
    }

    /**
     * Get all cache entries
     */
    public static function getAllEntries() {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return [];
        }

        return self::$cache->getAllEntries();
    }

    /**
     * Clean expired entries
     */
    public static function cleanExpired() {
        if (!self::$enabled || self::$cache === null) {
            self::init();
        }

        if (!self::$enabled) {
            return 0;
        }

        return self::$cache->cleanExpired();
    }

    /**
     * Generate cache key from endpoint and parameters
     */
    private static function generateKey($endpoint, $params) {
        // Remove skipCache from params if present
        $cacheParams = $params;
        unset($cacheParams['skipCache']);
        unset($cacheParams['skipcache']);

        // Sort params for consistent keys
        ksort($cacheParams);

        // Create key
        $keyData = $endpoint . '|' . json_encode($cacheParams);
        return 'api_' . md5($keyData);
    }

    /**
     * Check if caching should be skipped for this request
     */
    public static function shouldSkip($params) {
        return isset($params['skipCache']) ||
               isset($params['skipcache']) ||
               isset($params['skip_cache']);
    }

    /**
     * Enable/disable caching
     */
    public static function setEnabled($enabled) {
        self::$enabled = (bool)$enabled;
    }

    /**
     * Check if cache is enabled
     */
    public static function isEnabled() {
        return self::$enabled;
    }

    /**
     * Set default TTL
     */
    public static function setDefaultTTL($seconds) {
        self::$defaultTTL = max(0, (int)$seconds);
    }
}
