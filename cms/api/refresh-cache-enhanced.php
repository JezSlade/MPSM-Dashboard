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
 * SCHEDULE: Run hourly via cron (recommended: 02:00 daily)
 * RUNTIME: ~30 minutes for 5000+ devices
 * WARNING: Do NOT schedule more frequently than hourly to avoid overlapping runs
 */

set_time_limit(3600); // 60 minutes max for 5000+ devices
ini_set('memory_limit', '1G'); // 1GB for large datasets

require '../config.php';
require '../functions.php';
require_once dirname(__DIR__, 2) . '/bootstrap.php';

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
    if ($forceRun) {
        logMessage("Existing lock cleared (force=true, age={$lockAge}s)");
        unlink($lockFile);
    } else {
        // Do NOT auto-clear; avoid overlapping runs that truncate tables mid-refresh
        logMessage("Refresh skipped - already in progress (lock age {$lockAge}s). Use force=1 only if the run is truly stuck.");
        die(json_encode(['status' => 'skipped', 'reason' => 'refresh in progress', 'lock_age_seconds' => $lockAge]));
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

    // Step 2 & 3: Fetch and cache devices incrementally to prevent timeouts
    logMessage("Step 1: Fetching and caching devices (incremental mode)");
    // Pass whether we'll fetch drilldown so the incremental refresher can decide
    // whether it is safe to truncate the drilldown table.
    $deviceStats = fetchAndCacheAllDevicesIncremental($pdo, !$skipDrilldown);
    $stats['devices_cached'] = $deviceStats['total_devices'];
    $stats['deleted_devices'] = $deviceStats['deleted_devices'];
    logMessage("Cached {$stats['devices_cached']} devices total ({$stats['deleted_devices']} uninstalled)");
    if (!empty($deviceStats['device_cache_errors'])) {
        $stats['errors'] += $deviceStats['device_cache_errors'];
        logMessage("Cache insert errors: {$deviceStats['device_cache_errors']} (kept going)");
    }

    // Step 4: Fetch drill-down data for each device
    if ($skipDrilldown) {
        logMessage("Step 2: Drill-down fetch skipped by request");
    } else {
        logMessage("Step 2: Fetching drill-down data for all devices");

        // Load devices from database (already cached incrementally)
        $prefix = DB_PREFIX;
        $stmt = $pdo->query("SELECT serial_number, device_data FROM {$prefix}cache_devices");
        $drilldownQueue = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $device = json_decode($row['device_data'], true) ?: [];
            if (!isset($device['SerialNumber']) && isset($row['serial_number'])) {
                $device['SerialNumber'] = $row['serial_number'];
            }
            $drilldownQueue[] = $device;
        }
        logMessage("Loaded " . count($drilldownQueue) . " devices from cache for drill-down processing");
        $deviceAttempts = [];
        $processedCount = 0;
        $drilldownDelayMicroseconds = 250000; // 250ms between requests (increased to reduce rate limit hits)
        $drilldownErrors = 0;

        while (!empty($drilldownQueue)) {
            $device = array_shift($drilldownQueue);
            $serialNumber = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if (!$serialNumber) {
                $drilldownErrors++;
                continue;
            }

            try {
                $drillDownData = fetchDeviceDrillDown($device);

                if ($drillDownData) {
                    cacheDeviceDrilldown($serialNumber, $drillDownData);
                    $stats['devices_with_drilldown']++;
                } else {
                    $drilldownErrors++;
                    logMessage("Drill-down response empty for {$serialNumber}; skipping.");
                }

                $processedCount++;
                if ($processedCount % 50 === 0) {
                    logMessage("Progress: {$stats['devices_with_drilldown']} devices with drill-down cached ({$processedCount} attempted)");
                }

                usleep($drilldownDelayMicroseconds);
                unset($deviceAttempts[$serialNumber]);

            } catch (RateLimitException $e) {
                $attempts = ($deviceAttempts[$serialNumber] ?? 0) + 1;
                $deviceAttempts[$serialNumber] = $attempts;

                if ($attempts > 5) {
                    logMessage("Rate limit persisted for {$serialNumber} after {$attempts} attempts; deferring to next run.");
                    $drilldownErrors++;
                    unset($deviceAttempts[$serialNumber]);
                    continue;
                }

                logMessage("Rate limit fetching drill-down for {$serialNumber} (attempt {$attempts}). Sleeping {$e->getRetryAfter()}s then retrying.");
                sleep($e->getRetryAfter());
                $drilldownQueue[] = $device;

            } catch (Exception $e) {
                logMessage("Error fetching drill-down for {$serialNumber}: " . $e->getMessage());
                $drilldownErrors++;
            }
        }

        if ($drilldownErrors > 0) {
            $stats['errors'] += $drilldownErrors;
        }
    }

    // Step 5: Cache panel messages per device
    logMessage("Step 3: Caching panel message history");
    $stats['devices_with_panels'] = cachePanelMessages($pdo);

    // Step 6: Phase 2 - Cache connectors and page volume per customer
    logMessage("Step 4: Caching Phase 2 data (connectors, page volume)");
    $phase2Stats = cachePhase2Data($pdo);
    $stats['customers_cached_phase2'] = $phase2Stats['customers_cached'];
    $stats['phase2_errors'] = $phase2Stats['errors'];

    // Calculate stats
    $stats['duration'] = round(microtime(true) - $startTime, 2);

    // Health check validation
    $expectedMinimum = 4000;
    $healthWarnings = [];

    if ($stats['devices_cached'] < $expectedMinimum) {
        $warning = "Low device count: {$stats['devices_cached']} cached (expected {$expectedMinimum}+)";
        logMessage("⚠ WARNING: " . $warning);
        $healthWarnings[] = $warning;
    }

    if ($stats['devices_cached'] > 0 && !$skipDrilldown) {
        $drilldownCoverage = round(($stats['devices_with_drilldown'] / $stats['devices_cached']) * 100, 1);
        if ($drilldownCoverage < 80) {
            $warning = "Low drill-down coverage: {$drilldownCoverage}% (expected 80%+)";
            logMessage("⚠ WARNING: " . $warning);
            $healthWarnings[] = $warning;
        }
        $stats['drilldown_coverage_percent'] = $drilldownCoverage;
    } elseif ($skipDrilldown) {
        $stats['drilldown_coverage_percent'] = null;
    }

    $stats['health_warnings'] = $healthWarnings;
    $stats['health_status'] = empty($healthWarnings) ? 'HEALTHY' : 'WARNING';

    // Log completion
    logMessage("=== Cache refresh completed ===");
    logMessage("Health Status: {$stats['health_status']}");
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
            expires_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_customer (customer_code),
            INDEX idx_uninstalled (is_uninstalled),
            INDEX idx_cached (cached_at),
            INDEX idx_expires (expires_at)
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
            expires_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_serial (serial_number),
            INDEX idx_alerts (has_alerts),
            INDEX idx_cached (cached_at),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Call MPS API with retry/backoff and rate-limit awareness
 */
function callMpsWithRetry(string $action, array $params = [], int $maxAttempts = 5): ?array {
    global $stats;

    $baseDelaySeconds = 2;
    $lastError = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            $stats['api_calls_made']++;
            return callMPSAPI($action, $params);
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            $isRateLimit = stripos($lastError, '429') !== false || stripos($lastError, 'rate') !== false;
            $delay = min(60, (int)($baseDelaySeconds * $attempt));

            if ($isRateLimit) {
                $stats['rate_limit_retries']++;
                logMessage("Rate limit on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            } else {
                logMessage("API error on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            }

            if ($attempt === $maxAttempts) {
                if ($isRateLimit) {
                    throw new RateLimitException($lastError, $delay);
                }
                break;
            }

            sleep($delay);
        }
    }

    logMessage("API {$action} failed after {$maxAttempts} attempts: {$lastError}");
    return null;
}

/**
 * Fetch and cache devices incrementally to prevent memory/timeout issues
 * Caches every 50 pages (5000 devices) instead of waiting for all devices
 */
function fetchAndCacheAllDevicesIncremental(PDO $pdo, bool $willFetchDrilldown = true): array {
    global $stats;

    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;
    $maxPages = 500;
    $batchSizePages = 10; // Commit every 1000 devices
    $totalDevicesCached = 0;
    $totalDeletedDevices = 0;
    $deviceBatch = [];
    $seenSerials = [];
    $expectedRows = null;
    $expectedPages = null;
    $consecutiveEmptyPages = 0;
    $maxEmptyPages = 3;
    $deviceCacheErrors = 0;

    if (!$willFetchDrilldown) {
        logMessage("Quick warmup path: drill-down fetch disabled for this run.");
    }

    logMessage("Starting cache refresh with incremental commits (no upfront truncate)");

    // Fetch installed devices with incremental caching
    $installedBaseParams = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'FilterCustomerCodes' => null,
        'ProductBrand' => null,
        'ProductModel' => null,
        'OfficeId' => null,
        'Status' => null,
        'FilterText' => null,
        'PageRows' => $pageRows,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    for ($pageNumber = 1; $pageNumber <= $maxPages; $pageNumber++) {
        $params = $installedBaseParams;
        $params['PageNumber'] = $pageNumber;
        $params = array_filter($params, static function ($value) {
            return $value !== null;
        });

        try {
            $response = callMpsWithRetry('Device/List', $params);
        } catch (RateLimitException $e) {
            logMessage("Rate limit persisted on installed page {$pageNumber}: " . $e->getMessage());
            break;
        }

        if (!$response) {
            logMessage("Device/List returned empty response on page {$pageNumber}; stopping pagination.");
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            $consecutiveEmptyPages++;
            logMessage("Empty installed page {$pageNumber} (streak {$consecutiveEmptyPages}/{$maxEmptyPages})");

            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                logMessage("Pagination complete after {$maxEmptyPages} empty installed pages.");
                break;
            }
            continue;
        }

        $consecutiveEmptyPages = 0;
        $uniqueAdded = 0;
        $duplicatesThisPage = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? $device['serial_number'] ?? null;
            if ($serial === null) {
                continue;
            }
            if (isset($seenSerials[$serial])) {
                $duplicatesThisPage++;
                continue;
            }

            $seenSerials[$serial] = true;
            $deviceBatch[] = $device;
            $uniqueAdded++;
        }

        if ($expectedPages === null && isset($response['TotalRows'])) {
            $expectedRows = (int)$response['TotalRows'];
            $expectedPages = max(1, (int)ceil($expectedRows / $pageRows));
            logMessage("Detected TotalRows={$expectedRows}; expecting ~{$expectedPages} pages at {$pageRows} per page");
        }

        $stats['page_samples'][] = [
            'type' => 'installed',
            'page' => $pageNumber,
            'count' => $deviceCount,
            'unique_added' => $uniqueAdded,
            'duplicates' => $duplicatesThisPage
        ];

        $totalSoFar = count($seenSerials);
        logMessage("Page {$pageNumber}: Fetched {$deviceCount} devices ({$uniqueAdded} new, {$duplicatesThisPage} dupes). Unique total: {$totalSoFar}");

        if ($pageNumber % 10 === 0) {
            logMessage("=== PROGRESS: Page {$pageNumber}, Unique devices: {$totalSoFar} ===");
        }

        if ($pageNumber % $batchSizePages === 0 || $deviceCount < $pageRows) {
            if (!empty($deviceBatch)) {
                logMessage("Caching batch of " . count($deviceBatch) . " devices to database");

                $batchResult = cacheDeviceList($pdo, $deviceBatch);
                $totalDevicesCached += $batchResult['success'];
                $deviceCacheErrors += $batchResult['errors'];
                $deviceBatch = [];

                logMessage("Total devices cached so far: {$totalDevicesCached}");
            }
        }

        if ($expectedPages !== null && $pageNumber >= ($expectedPages + 1)) {
            logMessage("Stopping pagination at page {$pageNumber} (expected pages {$expectedPages}, TotalRows {$expectedRows})");
            break;
        }

        if ($deviceCount < $pageRows) {
            logMessage("Partial installed page ({$deviceCount}) detected; stopping after caching batch.");
            if (!empty($deviceBatch)) {
                $batchResult = cacheDeviceList($pdo, $deviceBatch);
                $totalDevicesCached += $batchResult['success'];
                $deviceCacheErrors += $batchResult['errors'];
                $deviceBatch = [];
            }
            break;
        }
    }

    if (!empty($deviceBatch)) {
        logMessage("Caching final batch of " . count($deviceBatch) . " devices to database");
        $batchResult = cacheDeviceList($pdo, $deviceBatch);
        $totalDevicesCached += $batchResult['success'];
        $deviceCacheErrors += $batchResult['errors'];
        $deviceBatch = [];
    }

    // Fetch deleted devices (marked as uninstalled)
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
            $response = callMpsWithRetry('Device/Deleted/ListByDealer', $params);
        } catch (RateLimitException $e) {
            logMessage("Rate limit persisted on deleted page {$deletedPageNumber}: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            logMessage("Device/Deleted/ListByDealer returned empty response on page {$deletedPageNumber}; stopping pagination.");
            break;
        }

        $pageDevices = extractDevicesFromResponse($response);
        if (empty($pageDevices)) {
            logMessage("Deleted device page {$deletedPageNumber} returned no devices; pagination complete.");
            break;
        }

        foreach ($pageDevices as &$device) {
            $device['IsUninstalled'] = true;
        }
        unset($device);

        $batchResult = cacheDeviceList($pdo, $pageDevices);
        $totalDeletedDevices += $batchResult['success'];
        $totalDevicesCached += $batchResult['success'];
        $deviceCacheErrors += $batchResult['errors'];

        $stats['page_samples'][] = [
            'type' => 'deleted',
            'page' => $deletedPageNumber,
            'count' => count($pageDevices)
        ];

        if (count($pageDevices) < 200) {
            logMessage("Deleted devices pagination completed at page {$deletedPageNumber} with fewer than 200 records.");
            break;
        }
    }

    if ($deviceCacheErrors > 0) {
        logMessage("Device cache errors encountered: {$deviceCacheErrors}");
    }

    return [
        'total_devices' => $totalDevicesCached,
        'deleted_devices' => $totalDeletedDevices,
        'unique_devices' => count($seenSerials),
        'device_cache_errors' => $deviceCacheErrors,
    ];
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

    $response = callMpsWithRetry('Device/Get', $params);

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
function cacheDeviceList(PDO $pdo, array $devices): array {
    $prefix = DB_PREFIX;
    $successCount = 0;
    $errorCount = 0;

    foreach ($devices as $device) {
        $serialNumber = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
        if (!$serialNumber) {
            continue;
        }

        $customerCode = $device['CustomerCode'] ?? $device['customerCode'] ?? null;
        $isUninstalled = isset($device['IsUninstalled']) && $device['IsUninstalled'] ? 1 : 0;

        try {
            $sql = "INSERT INTO {$prefix}cache_devices
                    (serial_number, device_data, customer_code, is_uninstalled, cached_at, expires_at)
                    VALUES (:serial, :data, :customer, :uninstalled, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))
                    ON DUPLICATE KEY UPDATE
                    device_data = :data2,
                    customer_code = :customer2,
                    is_uninstalled = :uninstalled2,
                    cached_at = NOW(),
                    expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)";

            $stmt = $pdo->prepare($sql);
            $deviceJson = json_encode($device);

            // Check JSON encoding success
            if ($deviceJson === false) {
                logMessage("ERROR: JSON encode failed for device {$serialNumber}: " . json_last_error_msg());
                $errorCount++;
                continue;
            }

            // Check JSON size (MySQL TEXT limit is 65,535 bytes)
            $jsonSize = strlen($deviceJson);
            if ($jsonSize > 65000) {
                logMessage("ERROR: Device {$serialNumber} JSON too large: {$jsonSize} bytes (limit 65,535)");
                $errorCount++;
                continue;
            }

            $result = $stmt->execute([
                ':serial' => $serialNumber,
                ':data' => $deviceJson,
                ':customer' => $customerCode,
                ':uninstalled' => $isUninstalled,
                ':data2' => $deviceJson,
                ':customer2' => $customerCode,
                ':uninstalled2' => $isUninstalled
            ]);

            if ($result) {
                $successCount++;
            } else {
                $errorInfo = $stmt->errorInfo();
                logMessage("ERROR: INSERT failed for {$serialNumber}: " . $errorInfo[2]);
                $errorCount++;
            }

        } catch (PDOException $e) {
            logMessage("ERROR: PDO exception caching {$serialNumber}: " . $e->getMessage());
            $errorCount++;
        }
    }

    // Log batch summary
    if ($errorCount > 0) {
        logMessage("Batch cache summary: {$successCount} succeeded, {$errorCount} failed");
    }

    return ['success' => $successCount, 'errors' => $errorCount];
}

/**
 * Phase 2: Cache connectors and page volume per customer
 * Fetches CustomerDashboard/Connectors and CustomerDashboard/Pages for each customer
 */
function cachePhase2Data(PDO $pdo): array {
    global $stats;
    $prefix = DB_PREFIX;
    $customersCached = 0;
    $errors = 0;

    // Get unique customer codes from cached devices
    $stmt = $pdo->query("
        SELECT DISTINCT customer_code
        FROM {$prefix}cache_devices
        WHERE customer_code IS NOT NULL AND customer_code != ''
        ORDER BY customer_code
    ");
    $customerCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    logMessage("Phase 2: Processing " . count($customerCodes) . " customers for connectors and page volume");

    // Check if Phase 2 tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_connectors'");
    $connectorsTableExists = $stmt->rowCount() > 0;
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_page_volume'");
    $volumeTableExists = $stmt->rowCount() > 0;

    if (!$connectorsTableExists || !$volumeTableExists) {
        logMessage("Phase 2 tables not initialized. Run init-phase2-cache-tables.php first. Skipping Phase 2 cache.");
        return ['customers_cached' => 0, 'errors' => 0];
    }

    // Truncate Phase 2 tables
    $pdo->exec("TRUNCATE TABLE {$prefix}cache_connectors");
    $pdo->exec("TRUNCATE TABLE {$prefix}cache_page_volume");

    foreach ($customerCodes as $customerCode) {
        try {
            // Fetch connectors
            $connectorsData = callMPSMAPI('CustomerDashboard/Connectors', ['Code' => $customerCode]);
            $stats['api_calls_made']++;

            if ($connectorsData) {
                // Cache connectors
                $stmt = $pdo->prepare("
                    INSERT INTO {$prefix}cache_connectors
                    (customer_code, total_win, total_embedded, last_day, sds_total_win, connector_data, cached_at)
                    VALUES (:customer_code, :total_win, :total_embedded, :last_day, :sds_total_win, :connector_data, NOW())
                    ON DUPLICATE KEY UPDATE
                        total_win = VALUES(total_win),
                        total_embedded = VALUES(total_embedded),
                        last_day = VALUES(last_day),
                        sds_total_win = VALUES(sds_total_win),
                        connector_data = VALUES(connector_data),
                        cached_at = NOW()
                ");
                $stmt->execute([
                    ':customer_code' => $customerCode,
                    ':total_win' => (int)($connectorsData['TotalWin'] ?? 0),
                    ':total_embedded' => (int)($connectorsData['TotalEmbedded'] ?? 0),
                    ':last_day' => (int)($connectorsData['LastDay'] ?? 0),
                    ':sds_total_win' => (int)($connectorsData['SdsTotalWin'] ?? 0),
                    ':connector_data' => json_encode($connectorsData)
                ]);
            }

            // Small delay to avoid rate limits
            usleep(200000); // 200ms

            // Fetch page volume
            $pagesData = callMPSMAPI('CustomerDashboard/Pages', ['code' => $customerCode]);
            $stats['api_calls_made']++;

            if ($pagesData) {
                // Cache page volume
                $stmt = $pdo->prepare("
                    INSERT INTO {$prefix}cache_page_volume
                    (customer_code, monthly_mono_managed, monthly_color_managed, monthly_mono_unmanaged, monthly_color_unmanaged, pages_data, cached_at)
                    VALUES (:customer_code, :mono_managed, :color_managed, :mono_unmanaged, :color_unmanaged, :pages_data, NOW())
                    ON DUPLICATE KEY UPDATE
                        monthly_mono_managed = VALUES(monthly_mono_managed),
                        monthly_color_managed = VALUES(monthly_color_managed),
                        monthly_mono_unmanaged = VALUES(monthly_mono_unmanaged),
                        monthly_color_unmanaged = VALUES(monthly_color_unmanaged),
                        pages_data = VALUES(pages_data),
                        cached_at = NOW()
                ");
                $stmt->execute([
                    ':customer_code' => $customerCode,
                    ':mono_managed' => (int)($pagesData['MonthlyMonoManaged'] ?? 0),
                    ':color_managed' => (int)($pagesData['MonthlyColorManaged'] ?? 0),
                    ':mono_unmanaged' => (int)($pagesData['MonthlyMonoUnManaged'] ?? 0),
                    ':color_unmanaged' => (int)($pagesData['MonthlyColorUnManaged'] ?? 0),
                    ':pages_data' => json_encode($pagesData)
                ]);
            }

            if ($connectorsData && $pagesData) {
                $customersCached++;
            }

            // Small delay between customers
            usleep(200000); // 200ms

        } catch (Exception $e) {
            logMessage("Phase 2 error for customer {$customerCode}: " . $e->getMessage());
            $errors++;
        }
    }

    logMessage("Phase 2 complete: {$customersCached} customers cached, {$errors} errors");
    return ['customers_cached' => $customersCached, 'errors' => $errors];
}

/**
 * Cache panel messages are already in mpsm_panel_messages table
 * Just count devices with panel history
 * Note: cacheDeviceDrillDown() function is now in cms/functions.php
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

/*
CHANGELOG
2025-11-10 Codex
- Bootstrapped the service container before the refresh run so `cacheDeviceDrillDown()` can resolve `DeviceRepository` via `app()` and persist drill-down rows.
*/

/*
CHANGELOG
2025-11-15 Codex
- Fixed device pagination to always flatten API responses via extractDevicesFromResponse() for installed and deleted pages.
- Restored dealer scoping in incremental fetch (FilterDealerId/FilterDealerCodes) and aligned PageRows to 100.
- Made drill-down truncation conditional: quick warmup (skipDrilldown=1) no longer wipes drilldown cache.
- Passed drilldown intent into fetchAndCacheAllDevicesIncremental() to control truncation behavior.
*/

/*
CHANGELOG
2025-12-06 Codex
- Hardened locking to avoid overlapping refresh runs; lock is only cleared with force=1.
- Added serial-based deduplication and duplicate-page detection to stop runaway pagination and keep totals correct.
- Logged per-page unique counts to improve observability of cache population.
- Swapped Device/List, Device/Deleted, and Device/Get calls to callMPSAPI (direct vendor) since mps-api/query returns empty data.
*/
