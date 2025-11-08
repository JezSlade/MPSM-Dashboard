<?php
/**
 * Get Drill-Down Cache Count
 * Simple endpoint to return exact device count with drill-down data
 */

require '../config.php';
require '../functions.php';
requireAuth();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
    $devicesTable = DB_PREFIX . 'cache_devices';

    // Get total devices
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$devicesTable}");
    $totalDevices = (int)$stmt->fetchColumn();

    // Get devices with drill-down
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$drilldownTable}");
    $drilldownCount = (int)$stmt->fetchColumn();

    // Calculate coverage
    $coverage = $totalDevices > 0 ? round(($drilldownCount / $totalDevices) * 100, 2) : 0;

    // Get freshness stats
    $stmt = $pdo->query("
        SELECT
            MIN(cached_at) as oldest,
            MAX(cached_at) as newest,
            SUM(CASE WHEN cached_at > NOW() - INTERVAL 1 HOUR THEN 1 ELSE 0 END) as last_hour,
            SUM(CASE WHEN cached_at > NOW() - INTERVAL 24 HOUR THEN 1 ELSE 0 END) as last_24h
        FROM {$drilldownTable}
    ");
    $freshness = $stmt->fetch(PDO::FETCH_ASSOC);

    jsonSuccess([
        'total_devices' => $totalDevices,
        'devices_with_drilldown' => $drilldownCount,
        'coverage_percent' => $coverage,
        'oldest_entry' => $freshness['oldest'],
        'newest_entry' => $freshness['newest'],
        'cached_last_hour' => (int)$freshness['last_hour'],
        'cached_last_24h' => (int)$freshness['last_24h']
    ]);

} catch (Exception $e) {
    jsonError('Failed to get drill-down count: ' . $e->getMessage(), 500);
}
