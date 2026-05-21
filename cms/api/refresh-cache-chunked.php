<?php
/**
 * Chunked Cache Refresh System - DUAL MODE (CLI + HTTP)
 *
 * Designed to work within web server timeout constraints by supporting
 * both CLI execution (no timeout) and HTTP monitoring (fast status checks).
 *
 * ARCHITECTURE:
 * - Uses staging tables (_staging suffix)
 * - Tracks progress in state file
 * - Each execution processes one chunk and exits
 * - CRON router (CLI) processes chunks without HTTP timeout
 * - HTTP endpoints provide fast status monitoring
 * - Atomic cutover when all chunks complete
 *
 * CLI USAGE (for CRON):
 * - php refresh-cache-chunked.php start
 * - php refresh-cache-chunked.php process
 * - php refresh-cache-chunked.php status
 *
 * HTTP USAGE (for monitoring):
 * - curl "...?action=start"
 * - curl "...?action=process" (WARNING: May timeout, use CLI instead)
 * - curl "...?action=status"
 */

// SHOW ALL ERRORS (development/debugging mode)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Detect execution mode - handle shared hosts that run CRON via cgi-fcgi wrapper
$sapi = PHP_SAPI;
$isCLI = (
    in_array($sapi, ['cli', 'phpdbg'], true) ||
    (empty($_SERVER['REQUEST_METHOD']) && empty($_SERVER['REMOTE_ADDR']))
);

set_time_limit(120); // 2 minutes max per chunk (HTTP only, CLI ignores this)
ini_set('memory_limit', '512M');

// Resolve paths differently for CLI vs HTTP
if ($isCLI) {
    // CLI: script is in cms/api/, need to go up to cms/
    $cmsRoot = dirname(__DIR__);
    require $cmsRoot . '/config.php';
    require $cmsRoot . '/functions.php';
    require_once __DIR__ . '/device-drilldown-enrichment.php';
    require_once dirname($cmsRoot) . '/bootstrap.php';
} else {
    // HTTP: already in cms/api/ context
    require '../config.php';
    require '../functions.php';
    require_once __DIR__ . '/device-drilldown-enrichment.php';
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
}

define('REFRESH_CACHE_CHUNKED_VERSION', '2025-12-04a');

$stateFile = __DIR__ . '/../locks/cache-refresh-state.json';
$logFile = __DIR__ . '/../logs/cache-refresh-' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function getState() {
    global $stateFile;
    if (!file_exists($stateFile)) {
        return null;
    }
    $json = file_get_contents($stateFile);
    return json_decode($json, true);
}

function saveState($state) {
    global $stateFile;
    $dir = dirname($stateFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
}

function respondJson($data) {
    global $isCLI;

    if (!array_key_exists('version', $data)) {
        $data['version'] = REFRESH_CACHE_CHUNKED_VERSION;
    }

    if ($isCLI) {
        // CLI: Output JSON to stdout
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        // HTTP: Send JSON with headers
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    exit;
}

// Trap PHP errors/exceptions so HTTP clients get JSON instead of blank 500s
set_error_handler(function($severity, $message, $file, $line) {
    logMessage("PHP ERROR [$severity] {$message} at {$file}:{$line}");
    respondJson([
        'success' => false,
        'error' => "PHP error: {$message}",
        'file' => $file,
        'line' => $line
    ]);
});

set_exception_handler(function(Throwable $e) {
    logMessage("UNHANDLED EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine());
    respondJson([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
});

/**
 * Call MPS API with retry/backoff and rate-limit awareness
 * Adapted from refresh-cache-enhanced.php (direct vendor calls)
 */
function callMpsApiWithRetry(string $action, array $params = [], int $maxAttempts = 5): ?array {
    global $stats;

    $baseDelaySeconds = 2;
    $lastError = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            if (!isset($stats['api_calls_made'])) {
                $stats['api_calls_made'] = 0;
            }
            $stats['api_calls_made']++;

            logMessage("[CACHE-CHUNKED-DIAG] Calling {$action} (attempt {$attempt}/{$maxAttempts})");
            $result = callMPSAPI($action, $params);
            logMessage("[CACHE-CHUNKED-DIAG] {$action} returned " . (is_array($result) ? count($result) . " items" : "null"));

            return $result;
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            $isRateLimit = stripos($lastError, '429') !== false || stripos($lastError, 'rate') !== false;
            $delay = min(60, (int)($baseDelaySeconds * $attempt));

            if ($isRateLimit) {
                if (!isset($stats['rate_limit_retries'])) {
                    $stats['rate_limit_retries'] = 0;
                }
                $stats['rate_limit_retries']++;
                logMessage("[CACHE-CHUNKED-DIAG] Rate limit on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            } else {
                logMessage("[CACHE-CHUNKED-DIAG] API error on {$action} (attempt {$attempt}/{$maxAttempts}): {$lastError}. Sleeping {$delay}s");
            }

            if ($attempt === $maxAttempts) {
                break;
            }

            sleep($delay);
        }
    }

    logMessage("[CACHE-CHUNKED-DIAG] API {$action} failed after {$maxAttempts} attempts: {$lastError}");
    return null;
}

if (!function_exists('callMpsQueryWithMeta')) {
    /**
     * Call mps-api/query and return the full decoded payload (including meta)
     */
    function callMpsQueryWithMeta(string $action, array $params = []): array {
        $payload = json_encode([
            'action' => $action,
            'params' => $params
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 25,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
        if ($response === false) {
            throw new Exception('Failed to reach mps-api/query');
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !($decoded['success'] ?? false)) {
            $errorMessage = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : 'Unknown query failure';
            throw new Exception("Query failed: {$errorMessage}");
        }

        return $decoded;
    }
}

// Reuse shared extractor if already defined in functions.php
if (!function_exists('extractDevicesFromResponse')) {
    /**
     * Extract devices from API response (handles multiple response formats)
     * Adapted from refresh-cache-enhanced.php
     */
    function extractDevicesFromResponse(array $response): array {
        // Format 1: {Result: [...], TotalRows: N}
        if (isset($response['Result']) && is_array($response['Result'])) {
            return $response['Result'];
        }

        // Format 2: {Items: [...]}
        if (isset($response['Items']) && is_array($response['Items'])) {
            return $response['Items'];
        }

        // Format 3: Direct array of devices
        if (array_keys($response) === range(0, count($response) - 1)) {
            return $response;
        }

        // Format 4: Empty or invalid
        return [];
    }
}

function resolveSerialColumn(PDO $pdo, string $table): string {
    $tableName = preg_replace('/[^a-z0-9_]/i', '', $table);

    if ($tableName === '') {
        throw new Exception("Invalid table name: {$table}");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName}");

    if (!$stmt) {
        throw new Exception("Unable to inspect columns for {$tableName}");
    }

    $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    foreach (['serial_number', 'device_serial'] as $candidate) {
        if (in_array($candidate, $columns, true)) {
            return $candidate;
        }
    }

    throw new Exception("Neither serial_number nor device_serial present on {$tableName}");
}

function ensureStateSerialColumns(PDO $pdo, array &$state, string $prefix) {
    if (empty($state['device_serial_column'])) {
        $state['device_serial_column'] = resolveSerialColumn($pdo, "{$prefix}cache_devices_staging");
    }

    if (empty($state['drilldown_serial_column'])) {
        $state['drilldown_serial_column'] = resolveSerialColumn($pdo, "{$prefix}cache_device_drilldown_staging");
    }
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return $stmt && $stmt->rowCount() > 0;
}

function tableCount(PDO $pdo, string $table): ?int {
    if (!tableExists($pdo, $table)) {
        return null;
    }
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM {$table}");
    if (!$stmt) {
        return null;
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($row['c']) ? (int)$row['c'] : null;
}

/**
 * Normalize persisted state for backward compatibility and deduplication.
 */
function hydrateState(array $state): array {
    $state['devices_to_fetch_drilldown'] = is_array($state['devices_to_fetch_drilldown'] ?? null) ? $state['devices_to_fetch_drilldown'] : [];
    $state['device_id_map'] = is_array($state['device_id_map'] ?? null) ? $state['device_id_map'] : [];
    $state['errors'] = is_array($state['errors'] ?? null) ? $state['errors'] : [];
    $state['seen_serials'] = is_array($state['seen_serials'] ?? null) ? $state['seen_serials'] : [];
    // If an older state exists, retro-deduplicate the drilldown queue and sync devices_cached to unique count.
    if (empty($state['seen_serials']) && !empty($state['devices_to_fetch_drilldown'])) {
        foreach ($state['devices_to_fetch_drilldown'] as $serial) {
            $state['seen_serials'][$serial] = true;
        }
        $state['devices_to_fetch_drilldown'] = array_keys($state['seen_serials']);
        $state['devices_cached'] = count($state['seen_serials']);
    }
    $state['duplicate_devices_skipped'] = $state['duplicate_devices_skipped'] ?? 0;
    $state['staging_counts'] = is_array($state['staging_counts'] ?? null) ? $state['staging_counts'] : [];
    return $state;
}

// ==================================================================
// DETERMINE ACTION (from CLI args or HTTP query)
// ==================================================================
if ($isCLI) {
    // CLI mode: action from first argument
    $action = $argv[1] ?? 'status';
} else {
    // HTTP mode: action from query string
    $action = $_GET['action'] ?? '';
}

// ==================================================================
// ACTION: ENRICH DRILL-DOWNS - Warm detailed maintenance sections
// ==================================================================
if ($action === 'enrich-drilldowns') {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $devicesTable = "{$prefix}cache_devices";
    $drilldownTable = "{$prefix}cache_device_drilldown";

    if (!tableExists($pdo, $devicesTable) || !tableExists($pdo, $drilldownTable)) {
        respondJson([
            'success' => false,
            'error' => 'Cache tables missing; cannot enrich drill-downs',
            'tables' => [
                $devicesTable => tableExists($pdo, $devicesTable),
                $drilldownTable => tableExists($pdo, $drilldownTable)
            ]
        ]);
    }

    $deviceSerialColumn = resolveSerialColumn($pdo, $devicesTable);
    $drilldownSerialColumn = resolveSerialColumn($pdo, $drilldownTable);
    $cliArg = $isCLI ? ($argv[2] ?? '') : '';
    $serialFilter = trim((string)($_GET['serialNumber'] ?? $_GET['serial'] ?? (!is_numeric($cliArg) ? $cliArg : '')));
    $limitInput = $isCLI
        ? (is_numeric($cliArg) ? (int)$cliArg : (int)($argv[3] ?? 3))
        : (int)($_GET['limit'] ?? 3);
    $limit = max(1, min(20, $limitInput ?: 3));

    $params = [];
    $where = "WHERE cd.device_data IS NOT NULL";
    if ($serialFilter !== '') {
        $where .= " AND cd.{$deviceSerialColumn} = :serial";
        $params[':serial'] = $serialFilter;
    } else {
        $where .= " AND (
            cdd.{$drilldownSerialColumn} IS NULL
            OR JSON_UNQUOTE(JSON_EXTRACT(cdd.drilldown_data, '$._mpsm.schemaVersion')) IS NULL
            OR JSON_UNQUOTE(JSON_EXTRACT(cdd.drilldown_data, '$._mpsm.schemaVersion')) <> '3'
        )";
    }

    $stmt = $pdo->prepare("
        SELECT
            cd.{$deviceSerialColumn} AS serial_number,
            cd.customer_code,
            cd.device_data,
            cdd.cached_at AS drilldown_cached_at
        FROM {$devicesTable} cd
        LEFT JOIN {$drilldownTable} cdd
            ON cdd.{$drilldownSerialColumn} = cd.{$deviceSerialColumn}
        {$where}
        ORDER BY (cdd.{$drilldownSerialColumn} IS NULL) DESC, cdd.cached_at ASC
        LIMIT :limit
    ");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $enriched = 0;
    $errors = [];
    $processed = [];

    foreach ($rows as $row) {
        $serial = (string)($row['serial_number'] ?? '');
        $device = json_decode((string)($row['device_data'] ?? ''), true);
        if ($serial === '' || !is_array($device)) {
            $errors[] = "Invalid cached device row for serial {$serial}";
            continue;
        }

        try {
            logMessage("Enriching drill-down {$serial}");
            $payload = mpsm_dd_enrich_device_payload($device, [
                'serialNumber' => $serial,
                'customerCode' => (string)($row['customer_code'] ?? '')
            ]);
            mpsm_dd_save_drilldown($pdo, $drilldownTable, $drilldownSerialColumn, $serial, $payload['drilldown']);
            $processed[] = [
                'serial_number' => $serial,
                'section_errors' => $payload['sectionErrors'] ?? []
            ];
            $enriched++;
            usleep(150000);
        } catch (Throwable $e) {
            $message = "Enrich {$serial} failed: " . $e->getMessage();
            logMessage($message);
            $errors[] = $message;
        }
    }

    respondJson([
        'success' => empty($errors),
        'action' => 'enrich-drilldowns',
        'limit' => $limit,
        'serial_filter' => $serialFilter ?: null,
        'selected' => count($rows),
        'enriched' => $enriched,
        'processed' => $processed,
        'errors' => $errors
    ]);
}

// ==================================================================
// ACTION: PROCESS - Process next chunk
// ==================================================================
if ($action === 'process' || $action === 'auto') {
    $state = getState();

    // Auto-start if no state exists (CRON-friendly)
    if (!$state) {
        logMessage("No active state found - auto-starting refresh");
        $action = 'start';
        // Fall through to start action below
    }
}

// Re-check action after potential auto-start
if ($action === 'start') {
    logMessage("=== CHUNKED REFRESH START ===");

    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Create/truncate staging tables (fail fast if live tables missing or creation fails)
    $liveDevices = "{$prefix}cache_devices";
    $liveDrill = "{$prefix}cache_device_drilldown";
    if (!tableExists($pdo, $liveDevices) || !tableExists($pdo, $liveDrill)) {
        respondJson([
            'success' => false,
            'error' => 'Live cache tables missing; cannot start staging refresh',
            'tables' => [
                $liveDevices => tableExists($pdo, $liveDevices),
                $liveDrill => tableExists($pdo, $liveDrill)
            ]
        ]);
    }

    try {
        logMessage("Creating staging tables");
        $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_devices_staging");
        $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_device_drilldown_staging");
        $pdo->exec("CREATE TABLE {$prefix}cache_devices_staging LIKE {$liveDevices}");
        $pdo->exec("CREATE TABLE {$prefix}cache_device_drilldown_staging LIKE {$liveDrill}");
    } catch (Exception $e) {
        respondJson([
            'success' => false,
            'error' => 'Failed to create staging tables',
            'message' => $e->getMessage()
        ]);
    }

    // Initialize state
    $state = [
        'status' => 'fetching_devices',
        'started_at' => date('Y-m-d H:i:s'),
        'current_page' => 1,
        'total_pages' => null,
        'devices_cached' => 0,
        'drilldowns_cached' => 0,
        'devices_to_fetch_drilldown' => [],
        'device_id_map' => [],  // Maps serial -> device Id for API calls
        'drilldown_index' => 0,
        'errors' => [],
        'last_activity' => date('Y-m-d H:i:s'),
        'seen_serials' => [],
        'duplicate_devices_skipped' => 0,
        'staging_counts' => []
    ];

    try {
        ensureStateSerialColumns($pdo, $state, $prefix);
        logMessage("Detected serial columns: {$state['device_serial_column']} / {$state['drilldown_serial_column']}");
    } catch (Exception $e) {
        $message = "Serial column detection failed: " . $e->getMessage();
        logMessage("CRITICAL: {$message}");
        respondJson([
            'success' => false,
            'error' => 'Serial column detection failed',
            'message' => $message
        ]);
    }

    saveState($state);
    logMessage("State initialized - starting from page 1");

    // Now process first chunk
    $action = 'process';
}

if ($action === 'process' || $action === 'auto') {
    $state = getState();

    // Should have state now after auto-start
    if (!$state) {
        respondJson([
            'success' => false,
            'error' => 'Failed to initialize state'
        ]);
    }
    $state = hydrateState($state);

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $stageDevices = "{$prefix}cache_devices_staging";
    $stageDrill = "{$prefix}cache_device_drilldown_staging";
    $chunkStartTime = microtime(true);

    try {
        ensureStateSerialColumns($pdo, $state, $prefix);
    } catch (Exception $e) {
        $message = "Serial column detection failed: " . $e->getMessage();
        logMessage("CRITICAL: {$message}");
        respondJson([
            'success' => false,
            'error' => 'Serial column detection failed',
            'message' => $message,
            'state' => $state
        ]);
    }

    $deviceSerialColumn = $state['device_serial_column'];
    $drilldownSerialColumn = $state['drilldown_serial_column'];

    // Fail fast if staging tables disappeared mid-run (protect live cache)
    if (!tableExists($pdo, $stageDevices) || !tableExists($pdo, $stageDrill)) {
        $state['status'] = 'staging_missing';
        $state['errors'][] = 'Staging tables missing during process step';
        saveState($state);
        respondJson([
            'success' => false,
            'error' => 'Staging tables missing; aborting process',
            'state' => $state,
            'counts' => [
                $stageDevices => tableCount($pdo, $stageDevices),
                $stageDrill => tableCount($pdo, $stageDrill),
                "{$prefix}cache_devices" => tableCount($pdo, "{$prefix}cache_devices"),
                "{$prefix}cache_device_drilldown" => tableCount($pdo, "{$prefix}cache_device_drilldown")
            ]
        ]);
    }

    // PHASE 1: Fetch device list pages
    if ($state['status'] === 'fetching_devices') {
        $page = $state['current_page'];
        $perPage = 100;

        logMessage("Fetching device list page {$page}");

        try {
            // Build API parameters (matching refresh-cache-enhanced.php pattern)
            $params = [
                'FilterDealerId' => DEFAULT_DEALER_ID,
                'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
                'FilterCustomerCodes' => null,
                'ProductBrand' => null,
                'ProductModel' => null,
                'OfficeId' => null,
                'Status' => null,
                'FilterText' => null,
                'PageNumber' => $page,
                'PageRows' => $perPage,
                'SortColumn' => 'Id',
                'SortOrder' => 0,
            ];

            // Remove null values
            $params = array_filter($params, static function ($value) {
                return $value !== null;
            });

            $response = null;
            $lastError = null;

            // Prefer mps-api/query for meta on the first page, but fall back to direct vendor API if it fails (page 2+ has been erroring)
            if ($page === 1) {
                try {
                    $response = callMpsQueryWithMeta('Device/List', $params);
                } catch (Exception $e) {
                    $lastError = $e->getMessage();
                    logMessage("Device/List page {$page} via query failed: {$lastError}; falling back to direct API");
                }
            }

            if ($response === null) {
                try {
                    $response = callMpsApiWithRetry('Device/List', $params);
                } catch (Exception $e) {
                    $lastError = $e->getMessage();
                    throw new Exception("Device/List page {$page} failed: {$lastError}");
                }
            }

            if (!is_array($response)) {
                throw new Exception("Invalid API response for page {$page}");
            }

            // mps-api/query returns {success, data, meta.total_rows}; legacy may return {Result, TotalRows}
            if (isset($response['data']) && is_array($response['data'])) {
                $devices = $response['data'];
                $totalRows = $response['meta']['total_rows'] ?? $response['TotalRows'] ?? 0;
            } elseif (isset($response['Result']) && is_array($response['Result'])) {
                $devices = $response['Result'];
                $totalRows = $response['TotalRows'] ?? 0;
            } elseif (isset($response[0]) && is_array($response[0])) {
                // Bare array of devices (meta missing)
                $devices = $response;
                $totalRows = 0;
            } else {
                throw new Exception("Invalid API response for page {$page}");
            }

            if ($totalRows > 0) {
                $totalPages = (int)ceil($totalRows / $perPage);
            } elseif (!empty($state['total_pages'])) {
                $totalPages = (int)$state['total_pages'];
            } else {
                // Meta missing; pessimistically assume there is at least one more page until we hit an empty page
                $totalPages = $page + 1;
            }

            if ($state['total_pages'] === null) {
                $state['total_pages'] = $totalPages;
                logMessage("Total pages detected: {$totalPages} (rows={$totalRows})");
            }

            // Cache devices to staging table
            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}cache_devices_staging
                ({$deviceSerialColumn}, customer_code, device_data, cached_at)
                VALUES (:serial, :customer, :data, NOW())
                ON DUPLICATE KEY UPDATE
                    customer_code = VALUES(customer_code),
                    device_data = VALUES(device_data),
                    cached_at = VALUES(cached_at)
            ");

            $deviceCount = count($devices);
            $uniqueAdded = 0;
            $duplicatesSkipped = 0;

            foreach ($devices as $device) {
                $serial = $device['SerialNumber'] ?? $device['serial'] ?? $device['deviceSerial'] ?? null;
                if (!$serial) continue;

                if (isset($state['seen_serials'][$serial])) {
                    $duplicatesSkipped++;
                    continue;
                }
                $state['seen_serials'][$serial] = true;

                $stmt->execute([
                    ':serial' => $serial,
                    ':customer' => $device['CustomerCode'] ?? $device['customerCode'] ?? $device['customer_code'] ?? null,
                    ':data' => json_encode($device, JSON_UNESCAPED_UNICODE)
                ]);

                $uniqueAdded++;
                $state['devices_cached']++;

                // Queue for drill-down fetch (skip only uninstalled devices)
                $installStatus = strtolower($device['InstallStatus'] ?? $device['installStatus'] ?? $device['install_status'] ?? '');
                if ($installStatus !== 'uninstalled') {
                    $state['devices_to_fetch_drilldown'][] = $serial;
                    // Store device ID for API calls (Device/Get requires Id, not SerialNumber)
                    $deviceId = $device['Id'] ?? $device['id'] ?? $device['deviceId'] ?? null;
                    if ($deviceId) {
                        $state['device_id_map'][$serial] = $deviceId;
                    }
                }
            }

            $state['duplicate_devices_skipped'] += $duplicatesSkipped;
            logMessage("Page {$page}/{$totalPages}: Cached {$uniqueAdded} new devices ({$duplicatesSkipped} duplicates skipped). Drilldown queue=" . count($state['devices_to_fetch_drilldown']));

            // If this page returned nothing, treat pagination as complete even if meta was missing
            if ($deviceCount === 0) {
                $state['status'] = 'fetching_drilldowns';
                $state['drilldown_index'] = 0;
                logMessage("Empty device page encountered at {$page}; moving to drill-down stage.");
            }

            // Move to next page or next phase
            if ($page >= $totalPages && $state['status'] === 'fetching_devices') {
                $state['status'] = 'fetching_drilldowns';
                $state['drilldown_index'] = 0;
                logMessage("Device fetch complete. Starting drill-down fetch for " . count($state['devices_to_fetch_drilldown']) . " devices");
            } elseif ($state['status'] === 'fetching_devices') {
                // Meta missing and we still got a full page? Extend the page budget to keep scanning.
                if ($totalRows === 0 && $deviceCount === $perPage && $page >= $state['total_pages']) {
                    $state['total_pages'] = $page + 1;
                    logMessage("Meta missing; extending total_pages to {$state['total_pages']} after full page {$page}");
                }
                $state['current_page']++;
            }

        } catch (Exception $e) {
            $error = "Page {$page} error: " . $e->getMessage();
            logMessage("ERROR: {$error}");
            $state['errors'][] = $error;
        }
    }

    // PHASE 2: Fetch drill-down data
    elseif ($state['status'] === 'fetching_drilldowns') {
        $state['devices_to_fetch_drilldown'] = array_values(array_unique($state['devices_to_fetch_drilldown']));
        $devicesToFetch = $state['devices_to_fetch_drilldown'];
        $deviceIdMap = $state['device_id_map'] ?? [];
        $chunkSize = 20; // Keep HTTP chunks below shared-host connection timeouts
        $totalToFetch = count($devicesToFetch);
        $chunkBudgetSeconds = 35; // Leave headroom for response serialization/state save

        while ($state['drilldown_index'] < $totalToFetch && (microtime(true) - $chunkStartTime) < $chunkBudgetSeconds) {
            $index = $state['drilldown_index'];
            $chunk = array_slice($devicesToFetch, $index, $chunkSize);
            if (empty($chunk)) {
                break;
            }

            logMessage("Fetching drill-downs {$index}-" . ($index + count($chunk)) . "/{$totalToFetch}");

            foreach ($chunk as $serial) {
                try {
                    // Get the device ID from our mapping (Device/Get requires Id, not SerialNumber)
                    $deviceId = $deviceIdMap[$serial] ?? null;

                    if (!$deviceId) {
                        // Fallback: try to get ID from staging table
                        $lookupStmt = $pdo->prepare("SELECT device_data FROM {$prefix}cache_devices_staging WHERE {$deviceSerialColumn} = :serial LIMIT 1");
                        $lookupStmt->execute([':serial' => $serial]);
                        $row = $lookupStmt->fetch(PDO::FETCH_ASSOC);
                        if ($row && !empty($row['device_data'])) {
                            $deviceData = json_decode($row['device_data'], true);
                            $deviceId = $deviceData['Id'] ?? $deviceData['id'] ?? null;
                        }
                    }

                    if (!$deviceId) {
                        logMessage("WARNING: No device ID found for serial {$serial}, skipping");
                        continue;
                    }

                    // Call Device/Get with the correct Id parameter
                    // Fetch drilldown via mps-api engine to avoid direct OAuth token issues
                    $apiResponse = callMPSQuery('Device/Get', ['Id' => $deviceId]);
                    if (!is_array($apiResponse)) {
                        logMessage("WARNING: Device {$serial} (ID: {$deviceId}) - Device/Get returned no data");
                        continue;
                    }

                    // MPS Cloud API wraps response in {Result: {...}, IsValid: bool, Errors: [...]}
                    // Extract the actual device data from the Result wrapper
                    $deviceGetResult = $apiResponse['Result'] ?? $apiResponse;

                    // Check for valid response - API returns device data with SerialNumber field
                    $responseSerial = $deviceGetResult['SerialNumber'] ?? $deviceGetResult['serial'] ?? $deviceGetResult['serialNumber'] ?? null;
                    $isValid = $apiResponse['IsValid'] ?? true;

                    if ($deviceGetResult && $responseSerial && $isValid) {
                        $wrappedDrilldown = mpsm_dd_wrap_device_get_payload($deviceGetResult, $apiResponse);
                        mpsm_dd_save_drilldown($pdo, "{$prefix}cache_device_drilldown_staging", $drilldownSerialColumn, $serial, $wrappedDrilldown);
                        $state['drilldowns_cached']++;
                    } else {
                        // Log why we skipped this device
                        $errors = $apiResponse['Errors'] ?? [];
                        $errorInfo = !empty($errors) ? json_encode($errors) : ($deviceGetResult['ErrorMessage'] ?? 'No SerialNumber in response');
                        logMessage("WARNING: Device {$serial} (ID: {$deviceId}) - {$errorInfo}");
                    }

                    // Rate limit protection
                    usleep(100000); // 100ms between requests

                } catch (Exception $e) {
                    $error = "Drill-down {$serial} error: " . $e->getMessage();
                    logMessage("WARNING: {$error}");
                    $state['errors'][] = $error;
                }
            }

            $state['drilldown_index'] += count($chunk);
        }

        if ($state['drilldown_index'] >= $totalToFetch) {
            // All drill-downs complete - move to cutover
            $state['staging_counts'] = [
                $stageDevices => tableCount($pdo, $stageDevices),
                $stageDrill => tableCount($pdo, $stageDrill)
            ];
            $state['status'] = 'ready_for_cutover';
            logMessage("Drill-down fetch complete. Ready for atomic cutover.");
        }
    }

    $state['last_activity'] = date('Y-m-d H:i:s');
    saveState($state);

    $chunkDuration = round(microtime(true) - $chunkStartTime, 2);
    logMessage("Chunk processed in {$chunkDuration}s");

    respondJson([
        'success' => true,
        'action' => 'process',
        'chunk_duration' => $chunkDuration,
        'state' => $state,
        'continue' => !in_array($state['status'], ['completed', 'cutover_failed', 'ready_for_cutover'])
    ]);
}

// ==================================================================
// ACTION: CUTOVER - Atomic swap staging -> live with verification
// ==================================================================
if ($action === 'cutover') {
    $state = getState();
    if (!$state) {
        respondJson(['success' => false, 'error' => 'No active state to cut over']);
    }
    $state = hydrateState($state);

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $liveDevices = "{$prefix}cache_devices";
    $liveDrill = "{$prefix}cache_device_drilldown";
    $stageDevices = "{$prefix}cache_devices_staging";
    $stageDrill = "{$prefix}cache_device_drilldown_staging";

    $stageDevicesCount = tableCount($pdo, $stageDevices);
    $stageDrillCount = tableCount($pdo, $stageDrill);
    $state['staging_counts'] = [
        $stageDevices => $stageDevicesCount,
        $stageDrill => $stageDrillCount
    ];

    if ($stageDevicesCount === null || $stageDrillCount === null) {
        respondJson([
            'success' => false,
            'error' => 'Staging tables missing; cannot cut over',
            'staging' => [
                $stageDevices => ['exists' => tableExists($pdo, $stageDevices)],
                $stageDrill => ['exists' => tableExists($pdo, $stageDrill)],
            ]
        ]);
    }

    if ($stageDevicesCount <= 0 || $stageDrillCount <= 0) {
        respondJson([
            'success' => false,
            'error' => 'Staging table is empty; aborting cutover',
            'staging_counts' => [
                $stageDevices => $stageDevicesCount,
                $stageDrill => $stageDrillCount
            ]
        ]);
    }

    try {
        logMessage("Performing guarded cutover (staging devices={$stageDevicesCount}, drilldowns={$stageDrillCount})");

        // Swap live->old, staging->live
        $pdo->exec("RENAME TABLE
            {$liveDevices} TO {$liveDevices}_old,
            {$stageDevices} TO {$liveDevices},
            {$liveDrill} TO {$liveDrill}_old,
            {$stageDrill} TO {$liveDrill}
        ");

        $newLiveDevices = tableCount($pdo, $liveDevices);
        $newLiveDrill = tableCount($pdo, $liveDrill);

        $valid = (
            $newLiveDevices !== null &&
            $newLiveDrill !== null &&
            $newLiveDevices >= $stageDevicesCount &&
            $newLiveDrill >= $stageDrillCount &&
            $newLiveDevices > 0 &&
            $newLiveDrill > 0
        );

        if ($valid) {
            // Drop old tables
            $pdo->exec("DROP TABLE IF EXISTS {$liveDevices}_old");
            $pdo->exec("DROP TABLE IF EXISTS {$liveDrill}_old");

            $state['status'] = 'completed';
            $state['completed_at'] = date('Y-m-d H:i:s');
            $state['live_counts_after_cutover'] = [
                $liveDevices => $newLiveDevices,
                $liveDrill => $newLiveDrill
            ];
            $duration = strtotime($state['completed_at']) - strtotime($state['started_at']);
            logMessage("=== CUTOVER COMPLETE === live_devices={$newLiveDevices} live_drilldowns={$newLiveDrill} duration={$duration}s");
        } else {
            // Restore live tables from _old; drop bad swap tables
            $pdo->exec("RENAME TABLE
                {$liveDevices} TO {$liveDevices}_bad,
                {$liveDevices}_old TO {$liveDevices},
                {$liveDrill} TO {$liveDrill}_bad,
                {$liveDrill}_old TO {$liveDrill}
            ");
            $pdo->exec("DROP TABLE IF EXISTS {$liveDevices}_bad");
            $pdo->exec("DROP TABLE IF EXISTS {$liveDrill}_bad");

            $state['status'] = 'cutover_failed';
            $state['errors'][] = "Cutover verification failed (new live devices={$newLiveDevices}, expected >= {$stageDevicesCount})";
            logMessage("CUTOVER VERIFICATION FAILED: live={$newLiveDevices}, staging={$stageDevicesCount}");
        }
    } catch (Exception $e) {
        $state['status'] = 'cutover_failed';
        $state['errors'][] = "Cutover exception: " . $e->getMessage();
        logMessage("CRITICAL CUTOVER EXCEPTION: " . $e->getMessage());
    }

    $state['last_activity'] = date('Y-m-d H:i:s');
    saveState($state);

    respondJson([
        'success' => $state['status'] === 'completed',
        'state' => $state,
        'live_counts' => [
            $liveDevices => tableCount($pdo, $liveDevices),
            $liveDrill => tableCount($pdo, $liveDrill),
        ],
        'staging_counts' => [
            $stageDevices => $stageDevicesCount,
            $stageDrill => $stageDrillCount
        ]
    ]);
}

// ==================================================================
// ACTION: STATUS - Check current progress
// ==================================================================
if ($action === 'status') {
    $state = getState();
    if ($state) {
        $state = hydrateState($state);
    }
    $pdo = null;
    try {
        $pdo = getDatabase();
    } catch (Exception $e) {
        // ignore for status
    }
    $prefix = DB_PREFIX;

    if (!$state) {
        respondJson([
            'success' => true,
            'status' => 'idle',
            'message' => 'No refresh in progress'
        ]);
    }

    respondJson([
        'success' => true,
        'state' => $state,
        'counts' => $pdo ? [
            "{$prefix}cache_devices" => tableCount($pdo, "{$prefix}cache_devices"),
            "{$prefix}cache_devices_staging" => tableCount($pdo, "{$prefix}cache_devices_staging"),
            "{$prefix}cache_device_drilldown" => tableCount($pdo, "{$prefix}cache_device_drilldown"),
            "{$prefix}cache_device_drilldown_staging" => tableCount($pdo, "{$prefix}cache_device_drilldown_staging"),
        ] : null
    ]);
}

// ==================================================================
// DEFAULT: Show usage
// ==================================================================
respondJson([
    'success' => false,
    'error' => 'Invalid action',
    'usage' => [
        'start' => '?action=start - Initialize new refresh',
        'process' => '?action=process - Process next chunk',
        'cutover' => '?action=cutover - Atomic swap staging to live with verification',
        'status' => '?action=status - Check progress',
        'auto' => '?action=auto - Auto-process (same as process)'
    ]
]);

/*
CHANGELOG
2025-11-22b Codex
- Fixed MPS Cloud API response parsing: Response wraps device in {Result: {...}, IsValid: bool}. Now extracts Result before checking SerialNumber.
2025-11-22a Codex
- Fixed drill-down fetch: Device/Get API requires `Id` parameter, not `SerialNumber`. Now stores device ID mapping during Stage 1 and uses it in Stage 2.
- Fixed response field check: API returns `SerialNumber` (PascalCase), not `serial` (lowercase). Now checks multiple case variants.
- Added fallback: if device ID not in state map, queries staging table to extract ID from cached device_data JSON.
- Added diagnostic logging when drill-down fetch fails to identify issues faster.
2025-11-18 Codex
- Fixed the staging INSERTs so they reference `serial_number`/`drilldown_data` (matching the actual cache schemas) instead of the obsolete `device_serial` columns, eliminating the SQLSTATE 42S22 cron error.
2025-11-19 Codex
- Added a `version` payload (constant `REFRESH_CACHE_CHUNKED_VERSION`) so cron emails can prove the deployed script is the updated one, plus logged the version in every JSON response.
- Added runtime detection of `serial_number` vs `device_serial` columns (plus state persistence) so the chunked refresh works even if the target cache table still uses the legacy column name.
- Queried and enqueued every device that is not explicitly `uninstalled` so the drill-down stage actually runs once the device list completes (earlier we only queued devices claiming to be `installed` and never executed stage 2).
2025-11-14 Codex
- Hardened CLI detection so cron executions running via cgi-fcgi wrappers skip HTTP headers and return pure JSON to the router.
2025-12-03 Codex
- Swapped Device/List and Device/Get fetches to use the mps-api engine via callMPSQuery to avoid direct OAuth token timeouts; keeps refresh running even when vendor token endpoint is slow.
- Normalized Device/List parsing to accept the mps-api/query shape ({success,data,meta.total_rows}) so pagination no longer flags valid pages as invalid.
2025-12-03 Codex
- Allow each invocation to process multiple drill-down chunks (90s budget) so helper/cron runs advance the queue faster without manual re-triggering.
2025-12-04 Codex
- Added callMpsQueryWithMeta() so stage 1 gets meta.total_rows from mps-api/query and pagination can iterate all pages.
- Added safeguards for missing meta: extend total_pages when pages are full, bail to drill-downs when an empty page arrives, and log rows/total_pages clearly.
- Bumped version to 2025-12-04a.
- Tuned drill-down chunk sizing/budget for shared hosting to reduce HTTP timeouts while still advancing refresh.
2025-12-04 Codex
- Completed atomic cutover; cache and drilldown staging swapped into live tables and old tables dropped.
2025-12-04 Codex
- Guarded extractDevicesFromResponse definition to avoid redeclare when functions.php is loaded.
2025-12-04 Codex
- Added global error/exception handlers to log and emit JSON errors instead of blank 500s during chunk processing.
2025-12-04 Codex
- Restored callMpsQueryWithMeta definition (guarded) to keep Device/List pagination working after refactor.
2025-12-04 Codex
- Added guarded staging creation on start, a dedicated cutover action with verification/restore, and status reporting of live/staging counts to prevent data loss during refresh.
2025-12-04 Codex
- Hydrated legacy state with deduplication to stop runaway drilldown queues and synced cached counts to unique devices.
- Enforced staging presence mid-run, re-deduped drilldown queues, and captured staging counts before cutover.
- Strengthened cutover validation to require populated drilldown tables and recorded live counts after the swap for verification.
2025-12-04 Codex
- Added fallback to direct Device/List vendor API when mps-api/query fails and guarded the first-page meta fetch to prevent pagination stalls.
*/
