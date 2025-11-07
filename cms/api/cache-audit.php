<?php
/**
 * Cache System Audit Script
 * Performs deep analysis of the caching system
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(300);
header('Content-Type: application/json');

$pdo = getDatabase();
$prefix = DB_PREFIX;

$audit = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tables' => [],
    'performance' => [],
    'issues' => [],
    'recommendations' => []
];

// Check cache table structures
$tables = [
    'cache_devices' => 'Device list cache',
    'cache_device_drilldown' => 'Device drill-down cache (meters, alerts, supplies)',
    'panel_messages' => 'Panel message history'
];

foreach ($tables as $table => $description) {
    $tableName = $prefix . $table;

    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        $exists = $stmt->fetch() !== false;

        if (!$exists) {
            $audit['issues'][] = "Table {$tableName} does not exist";
            continue;
        }

        // Get row count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$tableName}");
        $count = $stmt->fetchColumn();

        // Get table size
        $stmt = $pdo->query("
            SELECT
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = '" . DB_NAME . "'
            AND table_name = '{$tableName}'
        ");
        $sizeMb = $stmt->fetchColumn();

        // Get index info
        $stmt = $pdo->query("SHOW INDEX FROM {$tableName}");
        $indexes = $stmt->fetchAll();

        // Get sample data age
        $ageInfo = null;
        if ($table === 'cache_devices' || $table === 'cache_device_drilldown') {
            $stmt = $pdo->query("
                SELECT
                    MIN(cached_at) as oldest,
                    MAX(cached_at) as newest,
                    COUNT(*) as total
                FROM {$tableName}
            ");
            $ageInfo = $stmt->fetch();
        }

        $audit['tables'][$table] = [
            'exists' => true,
            'description' => $description,
            'row_count' => (int)$count,
            'size_mb' => (float)$sizeMb,
            'indexes' => count($indexes),
            'index_names' => array_unique(array_column($indexes, 'Key_name')),
            'age_info' => $ageInfo
        ];

    } catch (Exception $e) {
        $audit['issues'][] = "Error checking {$tableName}: " . $e->getMessage();
    }
}

// Performance checks
try {
    // Check device cache coverage
    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_uninstalled = 0 THEN 1 ELSE 0 END) as installed,
            SUM(CASE WHEN is_uninstalled = 1 THEN 1 ELSE 0 END) as uninstalled
        FROM {$prefix}cache_devices
    ");
    $deviceCoverage = $stmt->fetch();

    // Check drill-down coverage
    $stmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN has_alerts = 1 THEN 1 ELSE 0 END) as with_alerts,
            SUM(CASE WHEN has_supplies = 1 THEN 1 ELSE 0 END) as with_supplies
        FROM {$prefix}cache_device_drilldown
    ");
    $drilldownCoverage = $stmt->fetch();

    // Check panel message distribution
    $stmt = $pdo->query("
        SELECT
            COUNT(DISTINCT device_serial) as devices_with_messages,
            COUNT(*) as total_messages,
            MIN(received_at) as oldest_message,
            MAX(received_at) as newest_message
        FROM {$prefix}panel_messages
    ");
    $panelCoverage = $stmt->fetch();

    //Check for devices WITH panel messages but NO drill-down cache
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT pm.device_serial) as orphaned_devices
        FROM {$prefix}panel_messages pm
        LEFT JOIN {$prefix}cache_device_drilldown cdd ON pm.device_serial = cdd.serial_number
        WHERE cdd.serial_number IS NULL
    ");
    $orphanedDevices = $stmt->fetchColumn();

    // Check for devices WITH cache but NO panel messages
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT cd.serial_number) as devices_without_messages
        FROM {$prefix}cache_devices cd
        LEFT JOIN {$prefix}panel_messages pm ON cd.serial_number = pm.device_serial
        WHERE pm.device_serial IS NULL
    ");
    $devicesWithoutMessages = $stmt->fetchColumn();

    $audit['performance'] = [
        'device_cache' => $deviceCoverage,
        'drilldown_cache' => $drilldownCoverage,
        'panel_messages' => $panelCoverage,
        'orphaned_devices' => (int)$orphanedDevices,
        'devices_without_messages' => (int)$devicesWithoutMessages
    ];

    // Identify issues
    if ($orphanedDevices > 0) {
        $audit['issues'][] = "{$orphanedDevices} devices have panel messages but no drill-down cache";
        $audit['recommendations'][] = "Run cache refresh to populate drill-down data for all devices";
    }

    $drilldownTotal = $drilldownCoverage['total'] ?? 0;
    $deviceTotal = $deviceCoverage['total'] ?? 0;
    if ($drilldownTotal < $deviceTotal) {
        $missing = $deviceTotal - $drilldownTotal;
        $audit['issues'][] = "{$missing} devices missing drill-down cache ({$drilldownTotal}/{$deviceTotal})";
        $audit['recommendations'][] = "Enable full drill-down caching in refresh-cache-enhanced.php";
    }

} catch (Exception $e) {
    $audit['issues'][] = "Performance check error: " . $e->getMessage();
}

// Check cache freshness
try {
    $stmt = $pdo->query("
        SELECT
            TIMESTAMPDIFF(MINUTE, MAX(cached_at), NOW()) as minutes_since_last_cache
        FROM {$prefix}cache_devices
    ");
    $minutesSince = $stmt->fetchColumn();

    $audit['performance']['cache_freshness'] = [
        'minutes_since_last_refresh' => (int)$minutesSince,
        'status' => $minutesSince < 60 ? 'fresh' : ($minutesSince < 360 ? 'stale' : 'very_stale')
    ];

    if ($minutesSince > 60) {
        $audit['recommendations'][] = "Cache is {$minutesSince} minutes old. Consider running refresh more frequently.";
    }

} catch (Exception $e) {
    $audit['issues'][] = "Cache freshness check error: " . $e->getMessage();
}

// Sample some devices to check integration
try {
    $stmt = $pdo->query("
        SELECT
            cd.serial_number,
            cd.customer_code,
            cd.is_uninstalled,
            (SELECT COUNT(*) FROM {$prefix}panel_messages pm WHERE pm.device_serial = cd.serial_number) as panel_count,
            cdd.serial_number IS NOT NULL as has_drilldown
        FROM {$prefix}cache_devices cd
        LEFT JOIN {$prefix}cache_device_drilldown cdd ON cd.serial_number = cdd.serial_number
        LIMIT 10
    ");
    $samples = $stmt->fetchAll();

    $audit['samples'] = $samples;

} catch (Exception $e) {
    $audit['issues'][] = "Sample check error: " . $e->getMessage();
}

// Overall health score
$totalIssues = count($audit['issues']);
$audit['health_score'] = $totalIssues === 0 ? 'excellent' : ($totalIssues < 3 ? 'good' : ($totalIssues < 5 ? 'fair' : 'poor'));

echo json_encode($audit, JSON_PRETTY_PRINT);
