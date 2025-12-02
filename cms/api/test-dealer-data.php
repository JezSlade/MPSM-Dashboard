<?php
/**
 * Dealer Dashboard Data Sample Test
 * Shows sample metrics without authentication (for testing)
 */

require '../config.php';
require '../functions.php';

header('Content-Type: application/json');

try {
    $pdo = getDatabase();

    // Get device stats
    $deviceStats = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_uninstalled = 1 THEN 1 ELSE 0 END) as uninstalled,
            SUM(CASE WHEN is_uninstalled = 0 THEN 1 ELSE 0 END) as active
        FROM mpsm_cache_devices
    ")->fetch(PDO::FETCH_ASSOC);

    // Get offline count (cached > 24h ago, likely offline)
    $offlineCount = $pdo->query("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
        AND cached_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
    ")->fetch(PDO::FETCH_ASSOC);

    // Get ghost devices (7+ days not refreshed)
    $ghostCount = $pdo->query("
        SELECT COUNT(*) as count
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
        AND cached_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ")->fetch(PDO::FETCH_ASSOC);

    // Get duplicate IPs
    $dupIPs = $pdo->query("
        SELECT COUNT(*) as count
        FROM (
            SELECT JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) as ip
            FROM mpsm_cache_devices
            WHERE is_uninstalled = 0
            AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) IS NOT NULL
            AND JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) != ''
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress'))
            HAVING COUNT(*) > 1
        ) as dups
    ")->fetch(PDO::FETCH_ASSOC);

    // Get panel errors (24h)
    $panelErrors24h = $pdo->query("
        SELECT COUNT(*) as count
        FROM mpsm_panel_messages
        WHERE received_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ")->fetch(PDO::FETCH_ASSOC);

    $result = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'metrics' => [
            'totalDevices' => (int)$deviceStats['total'],
            'activeDevices' => (int)$deviceStats['active'],
            'uninstalledDevices' => (int)$deviceStats['uninstalled'],
            'offlineDevices24h' => (int)$offlineCount['count'],
            'ghostDevices7d' => (int)$ghostCount['count'],
            'duplicateIPs' => (int)$dupIPs['count'],
            'panelErrors24h' => (int)$panelErrors24h['count']
        ],
        'status' => 'Dashboard should show these values (NOT zeros)',
        'note' => 'If dashboard shows zeros, clear browser cache or force refresh'
    ];

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
