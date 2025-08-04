<?php
/**
 * SyncData.php
 * Pulls defined endpoints and caches curated data locally
 */

require_once __DIR__ . '/../core/ApiCaller.php';
require_once __DIR__ . '/../core/CacheManager.php';
require_once __DIR__ . '/../core/DataFormatter.php';

$map = include __DIR__ . '/../includes/CurationMap.php';

foreach ($map as $endpoint => $config) {
    try {
        $method = $config['method'];
        $payload = $config['payload'] ?? [];
        $query = $config['query'] ?? [];

        $result = ApiCaller::request($method, $endpoint, $payload, $query);

        if (!isset($result['body']['data'])) {
            throw new Exception("No data returned from $endpoint");
        }

        $formatted = is_callable($config['formatter'])
            ? call_user_func($config['formatter'], $result['body']['data'])
            : $result['body']['data'];

        CacheManager::put($config['cache_key'], $formatted, 300);

        echo "Synced: $endpoint\n";
    } catch (Exception $e) {
        echo "Failed syncing $endpoint: " . $e->getMessage() . "\n";
    }
}
