<?php
/**
 * ActionCache
 *
 * Lightweight file-based cache to accelerate high-volume MPS API actions.
 * Designed for quick deployment without schema changes; can be swapped for
 * database-backed storage later without touching callers.
 */

class ActionCache
{
    private const DEFAULT_TTL = 300; // 5 minutes
    private const CACHE_STRATEGY_ENV = 'MPS_CACHE_STRATEGY';
    private const CACHE_BYPASS_ENV = 'MPS_CACHE_FORCE_BYPASS';

    /** @var array<string,array<string,mixed>> */
    private static $actionConfig = [];

    /** @var string */
    private static $storageDir;

    /** @var bool */
    private static $initialized = false;

    /** @var string */
    private static $strategy = 'readthrough';

    /**
     * Initialise cache configuration.
     *
     * @param array<string,mixed> $config
     */
    public static function init(array $config = []): void
    {
        if (self::$initialized) {
            return;
        }

        self::$storageDir = __DIR__ . '/storage';
        if (!is_dir(self::$storageDir) && !@mkdir(self::$storageDir, 0775, true) && !is_dir(self::$storageDir)) {
            error_log('[ActionCache] Failed to create cache directory: ' . self::$storageDir);
        }

        // Extract special keys.
        if (isset($config['__strategy'])) {
            self::$strategy = self::sanitizeStrategy((string) $config['__strategy']);
            unset($config['__strategy']);
        }

        if (isset($config['__default_ttl'])) {
            $defaultTtl = (int) $config['__default_ttl'];
            if ($defaultTtl > 0) {
                self::$actionConfig['__default_ttl'] = $defaultTtl;
            }
            unset($config['__default_ttl']);
        }

        foreach ($config as $action => $settings) {
            if (!is_array($settings)) {
                continue;
            }
            $canonical = self::canonicalAction($action);
            self::$actionConfig[$canonical] = $settings;
        }

        // Environment overrides.
        $envStrategy = getenv(self::CACHE_STRATEGY_ENV);
        if ($envStrategy !== false) {
            self::$strategy = self::sanitizeStrategy($envStrategy);
        }
        $forceBypass = getenv(self::CACHE_BYPASS_ENV);
        if ($forceBypass !== false && self::toBool($forceBypass)) {
            self::$strategy = 'off';
        }

        self::$initialized = true;
    }

    /**
     * Determine if caching is globally enabled.
     */
    public static function isEnabled(): bool
    {
        return self::$initialized && self::$strategy !== 'off';
    }

    /**
     * Determine whether a request should bypass the cache due to control params.
     *
     * @param array<string,mixed> $params
     */
    public static function shouldBypassWithParams(array $params): bool
    {
        foreach (['skipCache', 'skip_cache', 'skipcache', 'cacheBypass'] as $flag) {
            if (array_key_exists($flag, $params)) {
                return self::toBool($params[$flag]);
            }
        }

        return false;
    }

    /**
     * Remove cache control parameters from request params.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public static function stripControlParams(array $params): array
    {
        foreach (['skipCache', 'skip_cache', 'skipcache', 'cacheBypass'] as $flag) {
            if (array_key_exists($flag, $params)) {
                unset($params[$flag]);
            }
        }
        return $params;
    }

    /**
     * Attempt to retrieve a cached response.
     *
     * @param string|null $actionName
     * @param string $method
     * @param string $endpoint
     * @param array<string,mixed> $query
     * @param array<mixed> $body
     * @param array<string,mixed> $options
     * @return array<string,mixed>|null
     */
    public static function getCachedResponse(
        ?string $actionName,
        string $method,
        string $endpoint,
        $query,
        $body,
        array $options = []
    ): ?array {
        if (!self::isEnabled() || self::$strategy === 'shadow') {
            return null;
        }

        if (!self::isCacheableAction($actionName, $endpoint)) {
            return null;
        }

        $cacheKey = self::buildCacheKey($actionName, $method, $endpoint, $query, $body);
        $filePath = self::cacheFilePath($cacheKey);

        if (!is_file($filePath)) {
            return null;
        }

        $raw = @file_get_contents($filePath);
        if ($raw === false) {
            return null;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return null;
        }

        $expiresAt = isset($payload['expires_at']) ? (int) $payload['expires_at'] : 0;
        if ($expiresAt !== 0 && $expiresAt < time()) {
            @unlink($filePath);
            return null;
        }

        $response = $payload['data'] ?? null;
        if (!is_array($response)) {
            return null;
        }

        $response['cache'] = [
            'hit' => true,
            'cached_at' => $payload['stored_at'] ?? null,
            'expires_at' => $expiresAt ?: null,
            'key' => $cacheKey,
            'strategy' => self::$strategy,
        ];

        // Stamp fresh timestamp/performance metadata to reflect cache hit.
        $response['timestamp'] = date('c');
        if (!isset($response['performance']) || !is_array($response['performance'])) {
            $response['performance'] = [];
        }
        $response['performance']['duration_ms'] = 5;
        $response['performance']['cache_hit'] = true;

        return $response;
    }

    /**
     * Persist a response to the cache store.
     *
     * @param string|null $actionName
     * @param string $method
     * @param string $endpoint
     * @param array<string,mixed> $query
     * @param array<mixed> $body
     * @param array<string,mixed> $response
     * @param array<string,mixed> $options
     */
    public static function storeResponse(
        ?string $actionName,
        string $method,
        string $endpoint,
        $query,
        $body,
        array $response,
        array $options = []
    ): void {
        if (!self::isEnabled()) {
            return;
        }
        if (!self::isCacheableAction($actionName, $endpoint)) {
            return;
        }
        if (($response['success'] ?? false) !== true) {
            return;
        }

        $cacheKey = self::buildCacheKey($actionName, $method, $endpoint, $query, $body);
        $filePath = self::cacheFilePath($cacheKey);

        $ttl = self::resolveTtl($actionName, $endpoint);
        $payload = [
            'stored_at' => time(),
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'action' => $actionName,
            'endpoint' => $endpoint,
            'method' => strtoupper($method),
            'data' => self::stripCacheMetadata($response),
        ];

        @file_put_contents($filePath, json_encode($payload, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    /**
     * Return configured warm parameter sets for background refresh.
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public static function getWarmParameterSets(): array
    {
        $map = [];
        foreach (self::$actionConfig as $action => $config) {
            if ($action === '__default_ttl') {
                continue;
            }

            $sets = $config['warm_params'] ?? $config['warm'] ?? null;
            if (!is_array($sets)) {
                continue;
            }
            $map[$action] = [];
            foreach ($sets as $entry) {
                $map[$action][] = is_array($entry) ? $entry : [];
            }
        }
        return $map;
    }

    /**
     * Remove metadata before storing.
     *
     * @param array<string,mixed> $response
     * @return array<string,mixed>
     */
    private static function stripCacheMetadata(array $response): array
    {
        if (isset($response['cache'])) {
            unset($response['cache']);
        }
        if (isset($response['performance']) && is_array($response['performance'])) {
            unset($response['performance']['cache_hit']);
        }
        return $response;
    }

    /**
     * Check whether action or endpoint is cacheable.
     */
    private static function isCacheableAction(?string $actionName, string $endpoint): bool
    {
        if (!self::$initialized) {
            return false;
        }

        if ($actionName !== null) {
            $canonical = self::canonicalAction($actionName);
            if (isset(self::$actionConfig[$canonical])) {
                return true;
            }
        }

        $endpointKey = self::canonicalAction($endpoint);
        return isset(self::$actionConfig[$endpointKey]);
    }

    /**
     * Resolve TTL for an action.
     */
    private static function resolveTtl(?string $actionName, string $endpoint): int
    {
        if ($actionName !== null) {
            $canonical = self::canonicalAction($actionName);
            if (isset(self::$actionConfig[$canonical]['ttl'])) {
                return max(0, (int) self::$actionConfig[$canonical]['ttl']);
            }
        }

        $endpointKey = self::canonicalAction($endpoint);
        if (isset(self::$actionConfig[$endpointKey]['ttl'])) {
            return max(0, (int) self::$actionConfig[$endpointKey]['ttl']);
        }

        return self::$actionConfig['__default_ttl'] ?? self::DEFAULT_TTL;
    }

    /**
     * Build deterministic cache key.
     *
     * @param array<string,mixed> $query
     * @param array<mixed> $body
     */
    private static function buildCacheKey(
        ?string $actionName,
        string $method,
        string $endpoint,
        $query,
        $body
    ): string {
        $queryNormalized = is_array($query) ? $query : ['__value' => $query];
        $bodyNormalized = is_array($body) ? $body : ['__value' => $body];

        $payload = [
            'action' => $actionName !== null ? self::canonicalAction($actionName) : null,
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'query' => self::normalizeStructure($queryNormalized),
            'body' => self::normalizeStructure($bodyNormalized),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Compute absolute path for cache key.
     */
    private static function cacheFilePath(string $cacheKey): string
    {
        return self::$storageDir . '/' . $cacheKey . '.json';
    }

    /**
     * Normalise arrays for consistent hashing.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeStructure($value)
    {
        if (is_array($value)) {
            if (self::isAssoc($value)) {
                ksort($value);
                foreach ($value as $k => $v) {
                    $value[$k] = self::normalizeStructure($v);
                }
            } else {
                foreach ($value as $idx => $entry) {
                    $value[$idx] = self::normalizeStructure($entry);
                }
            }
        } elseif ($value instanceof stdClass) {
            $value = self::normalizeStructure((array) $value);
        }

        return $value;
    }

    /**
     * Determine if array is associative.
     */
    private static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Canonicalise action/endpoint identifiers.
     */
    private static function canonicalAction(string $action): string
    {
        return strtolower(trim($action));
    }

    /**
     * Convert mixed value to boolean.
     *
     * @param mixed $value
     */
    private static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower((string) $value);
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Sanitize cache strategy.
     */
    private static function sanitizeStrategy(string $strategy): string
    {
        $normalized = strtolower(trim($strategy));
        if (!in_array($normalized, ['off', 'shadow', 'readthrough'], true)) {
            return 'readthrough';
        }
        return $normalized;
    }
}
