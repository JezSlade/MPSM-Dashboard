<?php
/**
 * Ultra-lightweight count - just JSON output
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json');

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
    $devices = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_device_drilldown");
    $drilldowns = (int)$stmt->fetchColumn();

    echo json_encode([
        'devices' => $devices,
        'drilldowns' => $drilldowns,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
