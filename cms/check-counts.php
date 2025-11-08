<?php
/**
 * Quick count checker
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/functions.php';

if (php_sapi_name() !== 'cli') {
    requireAuth();
    header('Content-Type: text/plain');
}

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    echo "=== DATABASE COUNTS ===\n\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
    $deviceCount = $stmt->fetchColumn();
    echo "Devices in cache: $deviceCount\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_device_drilldown");
    $drilldownCount = $stmt->fetchColumn();
    echo "Drill-downs in cache: $drilldownCount\n";

    $stmt = $pdo->query("SELECT MAX(cached_at) FROM {$prefix}cache_devices");
    $lastCached = $stmt->fetchColumn();
    echo "Last device cached: $lastCached\n";

    echo "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
