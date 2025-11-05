<?php
/**
 * Enhanced Background Cache Refresh System
 *
 * Caches ALL data for instant CMS population:
 * - Device lists (installed + uninstalled)
 * - Device drill-down details (meters, alerts, supplies)
 * - Panel message history per device
 * - Customer information
 * - Counter details
 *
 * Run every 5 minutes via cron or Task Scheduler
 */

set_time_limit(600); // 10 minutes max
ini_set('memory_limit', '512M');

require '../config.php';
require '../functions.php';

$startTime = microtime(true);
$stats = [
    'devices_cached' => 0,
    'devices_with_drilldown' => 0,
    'devices_with_panels' => 0,
    'api_calls_made' => 0,
    'errors' => 0,
    'duration' => 0
];

$lockFile = __DIR__ . '/cache/enhanced-refresh.lock';
$logFile = __DIR__ . '/../logs/cache-refresh-' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    error_log("[ENHANCED-CACHE] $message");
}

// Prevent concurrent refreshes
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge < 600) { // 10 minutes
        logMessage("Refresh skipped - already in progress");
        die(json_encode(['status' => 'skipped', 'reason' => 'refresh in progress']));
    }
    unlink($lockFile);
}

file_put_contents($lockFile, time());
logMessage("=== Starting enhanced cache refresh ===");

try {
    $pdo = getDatabase();

    // Step 1: Ensure cache tables exist
    ensureCacheTables($pdo);

    // Step 2: Fetch all devices (both installed and deleted)
    logMessage("Step 1: Fetching all devices");
    $devices = fetchAllDevices();
    $stats['devices_cached'] = count($devices);
    logMessage("Fetched {$stats['devices_cached']} devices total");

    // Step 3: Cache device list
    cacheDeviceList($pdo, $devices);

    // Step 4: Fetch drill-down data for each device
    logMessage("Step 2: Fetching drill-down data for all devices");
    foreach ($devices as $device) {
        $serialNumber = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
        if (!$serialNumber) {
            continue;
        }

        try {
            $drillDownData = fetchDeviceDrillDown($serialNumber);
            if ($drillDownData) {
                cacheDeviceDrillDown($pdo, $serialNumber, $drillDownData);
                $stats['devices_with_drilldown']++;
            }
            $stats['api_calls_made']++;

            // Rate limiting - don't overwhelm the API
            usleep(50000); // 50ms delay between requests

        } catch (Exception $e) {
            logMessage("Error fetching drill-down for $serialNumber: " . $e->getMessage());
            $stats['errors']++;
        }

        // Progress logging every 50 devices
        if ($stats['devices_with_drilldown'] % 50 === 0) {
            logMessage("Progress: {$stats['devices_with_drilldown']} devices processed");
        }
    }

    // Step 5: Cache panel messages per device
    logMessage("Step 3: Caching panel message history");
    $stats['devices_with_panels'] = cachePanelMessages($pdo);

    // Calculate stats
    $stats['duration'] = round(microtime(true) - $startTime, 2);

    // Log completion
    logMessage("=== Cache refresh completed ===");
    logMessage("Devices cached: {$stats['devices_cached']}");
    logMessage("Drill-down cached: {$stats['devices_with_drilldown']}");
    logMessage("Panel messages: {$stats['devices_with_panels']}");
    logMessage("API calls: {$stats['api_calls_made']}");
    logMessage("Errors: {$stats['errors']}");
    logMessage("Duration: {$stats['duration']}s");

    // Remove lock
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }

    die(json_encode([
        'status' => 'success',
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]));

} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}

/**
 * Ensure cache tables exist
 */
function ensureCacheTables(PDO $pdo): void {
    $prefix = DB_PREFIX;

    // Device list cache
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$prefix}cache_devices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serial_number VARCHAR(150) NOT NULL UNIQUE,
            device_data JSON NOT NULL,
            customer_code VARCHAR(100) NULL,
            is_uninstalled TINYINT(1) DEFAULT 0,
            cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_code),
            INDEX idx_uninstalled (is_uninstalled),
            INDEX idx_cached (cached_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Device drill-down cache (meters, alerts, supplies, counters)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$prefix}cache_device_drilldown (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serial_number VARCHAR(150) NOT NULL UNIQUE,
            drilldown_data JSON NOT NULL,
            has_alerts TINYINT(1) DEFAULT 0,
            has_supplies TINYINT(1) DEFAULT 0,
            cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_serial (serial_number),
            INDEX idx_alerts (has_alerts),
            INDEX idx_cached (cached_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Fetch all devices from MPSM API
 */
function fetchAllDevices(): array {
    global $stats;

    $dealerCode = DEFAULT_DEALER_CODE;
    $allDevices = [];

    // Fetch installed devices
    $installedParams = [
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    $pageNumber = 1;
    while ($pageNumber <= 50) {
        $installedParams['PageNumber'] = $pageNumber;

        $response = callMPSMAPI('Device/List', $installedParams);
        $stats['api_calls_made']++;

        if (!$response) {
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        if (empty($pageDevices)) {
            break;
        }

        $allDevices = array_merge($allDevices, $pageDevices);

        if (count($pageDevices) < 200) {
            break;
        }

        $pageNumber++;
    }

    // Fetch deleted/uninstalled devices
    $deletedParams = [
        'dealerCode' => $dealerCode,
        'pageNumber' => 1,
        'pageRows' => 200,
        'sortColumn' => 'AssetNumber',
        'sortOrder' => 'Asc'
    ];

    $deletedPageNumber = 1;
    while ($deletedPageNumber <= 20) {
        $deletedParams['pageNumber'] = $deletedPageNumber;

        $response = callMPSMAPI('Device/Deleted/List', $deletedParams);
        $stats['api_calls_made']++;

        if (!$response) {
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        if (empty($pageDevices)) {
            break;
        }

        // Mark as uninstalled
        foreach ($pageDevices as &$device) {
            $device['IsUninstalled'] = true;
        }

        $allDevices = array_merge($allDevices, $pageDevices);

        if (count($pageDevices) < 200) {
            break;
        }

        $deletedPageNumber++;
    }

    return $allDevices;
}

/**
 * Fetch device drill-down data from MPSM API
 */
function fetchDeviceDrillDown(string $serialNumber): ?array {
    $response = callMPSMAPI('Device/Get', ['serialNumber' => $serialNumber]);

    if (!$response || !isset($response['success']) || !$response['success']) {
        return null;
    }

    return $response['data'] ?? null;
}

/**
 * Call MPSM API endpoint
 */
function callMPSMAPI(string $action, array $params): ?array {
    $payload = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        return null;
    }

    return json_decode($response, true);
}

/**
 * Extract devices array from API response
 */
function extractDevicesFromResponse(array $response): array {
    if (!isset($response['data'])) {
        return [];
    }

    $raw = $response['data'];

    if (isset($raw['Items']) && is_array($raw['Items'])) {
        return $raw['Items'];
    }

    if (isset($raw['Result']) && is_array($raw['Result'])) {
        return $raw['Result'];
    }

    return [];
}

/**
 * Cache device list in database
 */
function cacheDeviceList(PDO $pdo, array $devices): void {
    $prefix = DB_PREFIX;

    foreach ($devices as $device) {
        $serialNumber = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
        if (!$serialNumber) {
            continue;
        }

        $customerCode = $device['CustomerCode'] ?? $device['customerCode'] ?? null;
        $isUninstalled = isset($device['IsUninstalled']) && $device['IsUninstalled'] ? 1 : 0;

        $sql = "INSERT INTO {$prefix}cache_devices
                (serial_number, device_data, customer_code, is_uninstalled, cached_at)
                VALUES (:serial, :data, :customer, :uninstalled, NOW())
                ON DUPLICATE KEY UPDATE
                device_data = :data2,
                customer_code = :customer2,
                is_uninstalled = :uninstalled2,
                cached_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $deviceJson = json_encode($device);

        $stmt->execute([
            ':serial' => $serialNumber,
            ':data' => $deviceJson,
            ':customer' => $customerCode,
            ':uninstalled' => $isUninstalled,
            ':data2' => $deviceJson,
            ':customer2' => $customerCode,
            ':uninstalled2' => $isUninstalled
        ]);
    }
}

/**
 * Cache device drill-down data
 */
function cacheDeviceDrillDown(PDO $pdo, string $serialNumber, array $drillDownData): void {
    $prefix = DB_PREFIX;

    $hasAlerts = !empty($drillDownData['supplyAlerts'] ?? []) ? 1 : 0;
    $hasSupplies = !empty($drillDownData['supplyLevels'] ?? []) ? 1 : 0;

    $sql = "INSERT INTO {$prefix}cache_device_drilldown
            (serial_number, drilldown_data, has_alerts, has_supplies, cached_at)
            VALUES (:serial, :data, :alerts, :supplies, NOW())
            ON DUPLICATE KEY UPDATE
            drilldown_data = :data2,
            has_alerts = :alerts2,
            has_supplies = :supplies2,
            cached_at = NOW()";

    $stmt = $pdo->prepare($sql);
    $dataJson = json_encode($drillDownData);

    $stmt->execute([
        ':serial' => $serialNumber,
        ':data' => $dataJson,
        ':alerts' => $hasAlerts,
        ':supplies' => $hasSupplies,
        ':data2' => $dataJson,
        ':alerts2' => $hasAlerts,
        ':supplies2' => $hasSupplies
    ]);
}

/**
 * Cache panel messages are already in mpsm_panel_messages table
 * Just count devices with panel history
 */
function cachePanelMessages(PDO $pdo): int {
    $prefix = DB_PREFIX;

    $sql = "SELECT COUNT(DISTINCT device_serial) as count
            FROM {$prefix}panel_messages
            WHERE device_serial IS NOT NULL";

    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return (int)($row['count'] ?? 0);
}
