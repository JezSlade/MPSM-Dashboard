<?php
/**
 * Sample Devices - Get real device data to analyze serial patterns
 */

require '../config.php';
require '../functions.php';

$bypassSecret = 'DEALER_API_2025';
$providedSecret = $_GET['secret'] ?? '';

if ($providedSecret !== $bypassSecret) {
    requireAuth();
}

header('Content-Type: application/json');

try {
    $pdo = getDatabase();

    // Get sample devices with manufacturer and serial info
    $stmt = $pdo->query("
        SELECT
            serial_number,
            COALESCE(
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Manufacturer')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Make')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Vendor')),
                JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Brand'))
            ) AS manufacturer,
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Model')) as model,
            JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Install')) as install_date
        FROM mpsm_cache_devices
        WHERE is_uninstalled = 0
        LIMIT 100
    ");

    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by manufacturer
    $byManufacturer = [];
    foreach ($devices as $device) {
        $mfg = strtoupper(trim($device['manufacturer'] ?? 'UNKNOWN'));
        if (!isset($byManufacturer[$mfg])) {
            $byManufacturer[$mfg] = [
                'count' => 0,
                'samples' => []
            ];
        }
        $byManufacturer[$mfg]['count']++;
        if (count($byManufacturer[$mfg]['samples']) < 5) {
            $byManufacturer[$mfg]['samples'][] = [
                'serial' => $device['serial_number'],
                'model' => $device['model'],
                'install' => $device['install_date']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'total_devices' => count($devices),
        'manufacturers' => $byManufacturer
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
