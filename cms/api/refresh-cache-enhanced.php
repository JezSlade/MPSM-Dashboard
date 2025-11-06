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
    'deleted_devices' => 0,
    'devices_with_drilldown' => 0,
    'devices_with_panels' => 0,
    'api_calls_made' => 0,
    'page_samples' => [],
    'drilldown_skipped' => false,
    'errors' => 0,
    'rate_limit_retries' => 0,
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

class RateLimitException extends RuntimeException
{
    private int $retryAfter;

    public function __construct(string $message, int $retryAfter = 15)
    {
        parent::__construct($message);
        $this->retryAfter = max(1, $retryAfter);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}

// Prevent concurrent refreshes (allow manual override via ?force=1)
$forceRun = isset($_GET['force']) && $_GET['force'] === '1';
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($forceRun || $lockAge >= 600) {
        logMessage("Existing lock cleared (force=" . ($forceRun ? 'true' : 'false') . ", age={$lockAge}s)");
        unlink($lockFile);
    } else {
        logMessage("Refresh skipped - already in progress");
        die(json_encode(['status' => 'skipped', 'reason' => 'refresh in progress']));
    }
}

file_put_contents($lockFile, time());
logMessage("=== Starting enhanced cache refresh ===");

try {
    $pdo = getDatabase();
    $skipDrilldown = isset($_GET['skipDrilldown']) && $_GET['skipDrilldown'] === '1';
    $stats['drilldown_skipped'] = $skipDrilldown;

    // Step 1: Ensure cache tables exist
    ensureCacheTables($pdo);

    // Step 2: Fetch all devices (both installed and deleted)
    logMessage("Step 1: Fetching all devices");
    $devices = fetchAllDevices();
    $stats['devices_cached'] = count($devices);
    $stats['deleted_devices'] = array_reduce($devices, static function ($carry, $device) {
        return $carry + (!empty($device['IsUninstalled']) ? 1 : 0);
    }, 0);
    logMessage("Fetched {$stats['devices_cached']} devices total ({$stats['deleted_devices']} uninstalled)");

    // Step 3: Cache device list
    cacheDeviceList($pdo, $devices);

    // Step 4: Fetch drill-down data for each device
    if ($skipDrilldown) {
        logMessage("Step 2: Drill-down fetch skipped by request");
    } else {
        logMessage("Step 2: Fetching drill-down data for all devices");
        $drilldownQueue = array_values($devices);
        $deviceAttempts = [];
        $processedCount = 0;
        $drilldownDelayMicroseconds = 50000; // 50ms between requests

        while (!empty($drilldownQueue)) {
            $device = array_shift($drilldownQueue);
            $serialNumber = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if (!$serialNumber) {
                continue;
            }

            try {
                $drillDownData = fetchDeviceDrillDown($device);
                $stats['api_calls_made']++;

                if ($drillDownData) {
                    cacheDeviceDrillDown($pdo, $serialNumber, $drillDownData);
                    $stats['devices_with_drilldown']++;
                }

                $processedCount++;
                if ($processedCount % 50 === 0) {
                    logMessage("Progress: {$stats['devices_with_drilldown']} devices with drill-down cached ({$processedCount} attempted)");
                }

                usleep($drilldownDelayMicroseconds);
                unset($deviceAttempts[$serialNumber]);

            } catch (RateLimitException $e) {
                $stats['rate_limit_retries']++;
                $attempts = ($deviceAttempts[$serialNumber] ?? 0) + 1;
                $deviceAttempts[$serialNumber] = $attempts;

                if ($attempts > 6) {
                    logMessage("Rate limit persisted for {$serialNumber} after {$attempts} attempts; deferring to next run.");
                    $stats['errors']++;
                    unset($deviceAttempts[$serialNumber]);
                    continue;
                }

                logMessage("Rate limit fetching drill-down for {$serialNumber} (attempt {$attempts}). Sleeping {$e->getRetryAfter()}s then retrying.");
                sleep($e->getRetryAfter());
                $drilldownQueue[] = $device;

            } catch (Exception $e) {
                logMessage("Error fetching drill-down for {$serialNumber}: " . $e->getMessage());
                $stats['errors']++;
            }
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
    logMessage("Deleted devices cached: {$stats['deleted_devices']}");
    logMessage("Drill-down cached: {$stats['devices_with_drilldown']}");
    logMessage("Panel messages: {$stats['devices_with_panels']}");
    logMessage("API calls: {$stats['api_calls_made']}");
    logMessage("Rate limit retries: {$stats['rate_limit_retries']}");
    logMessage("Errors: {$stats['errors']}");
    logMessage("Duration: {$stats['duration']}s");

    // Remove lock
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }

    die(json_encode([
        'status' => 'success',
        'stats' => $stats,
        'page_samples' => $stats['page_samples'],
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

    // Fetch installed devices across entire dealer (mirrors working params from get-cached-devices.php)
    $installedBaseParams = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'FilterCustomerCodes' => null,
        'ProductBrand' => null,
        'ProductModel' => null,
        'OfficeId' => null,
        'Status' => null,
        'FilterText' => null,
        'PageRows' => 50,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    for ($pageNumber = 1; $pageNumber <= 50; $pageNumber++) {
        $params = $installedBaseParams;
        $params['PageNumber'] = $pageNumber;
        $params = array_filter($params, static function ($value) {
            return $value !== null;
        });

        try {
            $response = callMPSMAPI('Device/List', $params);
        } catch (RateLimitException $e) {
            $stats['rate_limit_retries']++;
            logMessage("Rate limit while fetching installed device page {$pageNumber}; cooling {$e->getRetryAfter()}s before retry.");
            sleep($e->getRetryAfter());
            $pageNumber--;
            continue;
        }
        $stats['api_calls_made']++;

        if (!$response) {
            logMessage("Device/List returned empty response on page {$pageNumber}; stopping pagination.");
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        if (empty($pageDevices)) {
            logMessage("Device/List page {$pageNumber} returned no devices; pagination complete.");
            break;
        }

        $stats['page_samples'][] = [
            'type' => 'installed',
            'page' => $pageNumber,
            'count' => count($pageDevices)
        ];

        $allDevices = array_merge($allDevices, $pageDevices);

        if (count($pageDevices) < 50) {
            logMessage("Installed devices pagination completed at page {$pageNumber} with fewer than 50 records.");
            break;
        }
    }

    // Fetch deleted/uninstalled devices
    $deletedBaseParams = [
        'DealerCode' => $dealerCode,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    for ($deletedPageNumber = 1; $deletedPageNumber <= 20; $deletedPageNumber++) {
        $params = $deletedBaseParams;
        $params['PageNumber'] = $deletedPageNumber;

        try {
            $response = callMPSMAPI('Device/Deleted/ListByDealer', $params);
        } catch (RateLimitException $e) {
            $stats['rate_limit_retries']++;
            logMessage("Rate limit while fetching deleted device page {$deletedPageNumber}; cooling {$e->getRetryAfter()}s before retry.");
            sleep($e->getRetryAfter());
            $deletedPageNumber--;
            continue;
        }
        $stats['api_calls_made']++;

        if (!$response) {
            logMessage("Device/Deleted/ListByDealer returned empty response on page {$deletedPageNumber}; stopping pagination.");
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        if (empty($pageDevices)) {
            logMessage("Deleted device page {$deletedPageNumber} returned no devices; pagination complete.");
            break;
        }

        $stats['page_samples'][] = [
            'type' => 'deleted',
            'page' => $deletedPageNumber,
            'count' => count($pageDevices)
        ];

        foreach ($pageDevices as &$device) {
            $device['IsUninstalled'] = true;
        }
        unset($device);

        $allDevices = array_merge($allDevices, $pageDevices);

        if (count($pageDevices) < 200) {
            logMessage("Deleted devices pagination completed at page {$deletedPageNumber} with fewer than 200 records.");
            break;
        }
    }

    return $allDevices;
}

/**
 * Fetch device drill-down data from MPSM API
 */
function fetchDeviceDrillDown(array $device): ?array {
    $params = [];

    if (!empty($device['Id'])) {
        $params['Id'] = $device['Id'];
    } elseif (!empty($device['DeviceId'])) {
        $params['Id'] = $device['DeviceId'];
    } elseif (!empty($device['SerialNumber'])) {
        $params['SerialNumber'] = $device['SerialNumber'];
    } elseif (!empty($device['serialNumber'])) {
        $params['SerialNumber'] = $device['serialNumber'];
    } else {
        return null;
    }

    $response = callMPSMAPI('Device/Get', $params);

    return is_array($response) ? $response : null;
}

/**
 * Call MPSM API endpoint
 */
function callMPSMAPI(string $action, array $params): ?array {
    $maxAttempts = 5;
    $baseDelayMicroseconds = 750000; // 0.75s
    $lastError = null;
    $lastRateLimit = false;
    $retryAfterSeconds = 10;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $payload = json_encode([
            'action' => $action,
            'params' => $params
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
        $headers = $http_response_header ?? [];
        $httpCode = null;
        if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
            $httpCode = (int)$matches[1];
        }

        if ($response === false) {
            $lastError = "Transport failure (HTTP {$httpCode})";
            logMessage("API call [$action] failed to connect (attempt {$attempt}, HTTP {$httpCode})");
            if ($attempt < $maxAttempts) {
                usleep((int)($baseDelayMicroseconds * pow(2, $attempt - 1)));
                continue;
            }
            break;
        }

        $decoded = json_decode($response, true);
        $success = is_array($decoded) && !empty($decoded['success']);
        $errorMessage = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : null;

        if ($success) {
            $data = $decoded['data'] ?? null;
            $count = 0;
            if (is_array($data)) {
                if (isset($data['Items']) && is_array($data['Items'])) {
                    $count = count($data['Items']);
                } elseif (isset($data['Result']) && is_array($data['Result'])) {
                    $count = count($data['Result']);
                } elseif (array_keys($data) === range(0, count($data) - 1)) {
                    $count = count($data);
                }
            }
            logMessage("API call [$action] returned {$count} records");
            return $data;
        }

        $rateLimitedThisAttempt = ($httpCode === 429) ||
            (is_string($errorMessage) && stripos($errorMessage, 'rate limit') !== false);
        $lastRateLimit = $rateLimitedThisAttempt;

        if ($rateLimitedThisAttempt) {
            $retryAfterHeader = parseRetryAfterHeader($headers);
            if ($retryAfterHeader !== null) {
                $retryAfterSeconds = max($retryAfterSeconds, $retryAfterHeader);
            } else {
                $retryAfterSeconds = max($retryAfterSeconds, (int)ceil(pow(2, $attempt)));
            }

            if ($attempt < $maxAttempts) {
                logMessage("API rate limit hit for [$action] (attempt {$attempt}); sleeping {$retryAfterSeconds}s before retry.");
                sleep($retryAfterSeconds);
                continue;
            }

            logMessage("API rate limit persisted for [$action] after {$attempt} attempts.");
            break;
        }

        $lastError = $errorMessage ?: 'Unexpected response payload';
        logMessage("API error [$action]: " . ($errorMessage ?: 'Unexpected payload'));

        if ($attempt < $maxAttempts) {
            usleep((int)($baseDelayMicroseconds * pow(2, $attempt - 1)));
            continue;
        }

        break;
    }

    if ($lastRateLimit) {
        throw new RateLimitException("Rate limit exceeded for {$action}", $retryAfterSeconds);
    }

    if ($lastError) {
        logMessage("API call [$action] failed after {$maxAttempts} attempts: {$lastError}");
    }

    return null;
}

function parseRetryAfterHeader(array $headers): ?int {
    foreach ($headers as $headerLine) {
        if (stripos($headerLine, 'Retry-After:') === 0) {
            $value = trim(substr($headerLine, strlen('Retry-After:')));
            if ($value === '') {
                return null;
            }

            if (is_numeric($value)) {
                $seconds = (int)$value;
                return $seconds > 0 ? $seconds : null;
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                $diff = $timestamp - time();
                if ($diff > 0) {
                    return (int)$diff;
                }
            }
        }
    }

    return null;
}

// Note: extractDevicesFromResponse() is now defined in cms/functions.php (shared utility)

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
