<?php
/**
 * Quick Device Count - Direct DB Query
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

try {
    $pdo = getDatabase();

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_devices");
    $deviceCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "cache_device_drilldown");
    $drilldownCount = $stmt->fetchColumn();

    echo "DEVICES: $deviceCount\n";
    echo "DRILLDOWN: $drilldownCount\n";
    echo "COVERAGE: " . round(($drilldownCount / max($deviceCount, 1)) * 100, 2) . "%\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
