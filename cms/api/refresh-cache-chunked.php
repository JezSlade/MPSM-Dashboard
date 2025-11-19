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
    require_once dirname($cmsRoot) . '/bootstrap.php';
} else {
    // HTTP: already in cms/api/ context
    require '../config.php';
    require '../functions.php';
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
}

define('REFRESH_CACHE_CHUNKED_VERSION', '2025-11-19a');

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

    // Create staging tables
    logMessage("Creating staging tables");
    $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_devices_staging");
    $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_device_drilldown_staging");
    $pdo->exec("CREATE TABLE {$prefix}cache_devices_staging LIKE {$prefix}cache_devices");
    $pdo->exec("CREATE TABLE {$prefix}cache_device_drilldown_staging LIKE {$prefix}cache_device_drilldown");

    // Initialize state
    $state = [
        'status' => 'fetching_devices',
        'started_at' => date('Y-m-d H:i:s'),
        'current_page' => 1,
        'total_pages' => null,
        'devices_cached' => 0,
        'drilldowns_cached' => 0,
        'devices_to_fetch_drilldown' => [],
        'drilldown_index' => 0,
        'errors' => [],
        'last_activity' => date('Y-m-d H:i:s')
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

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
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

            // Call the API using the correct function
            $response = callMPSAPI('Device/List', $params);

            if (!$response || !isset($response['Result'])) {
                throw new Exception("Invalid API response for page {$page}");
            }

            $devices = $response['Result'];
            $totalRows = $response['TotalRows'] ?? 0;
            $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;

            if ($state['total_pages'] === null) {
                $state['total_pages'] = $totalPages;
                logMessage("Total pages detected: {$totalPages}");
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

            foreach ($devices as $device) {
                $serial = $device['SerialNumber'] ?? $device['serial'] ?? $device['deviceSerial'] ?? null;
                if (!$serial) continue;

                $stmt->execute([
                    ':serial' => $serial,
                    ':customer' => $device['CustomerCode'] ?? $device['customerCode'] ?? $device['customer_code'] ?? null,
                    ':data' => json_encode($device, JSON_UNESCAPED_UNICODE)
                ]);

                $state['devices_cached']++;

                // Queue for drill-down fetch (only installed devices)
                $installStatus = strtolower($device['InstallStatus'] ?? $device['installStatus'] ?? $device['install_status'] ?? '');
                if ($installStatus === 'installed') {
                    $state['devices_to_fetch_drilldown'][] = $serial;
                }
            }

            logMessage("Page {$page}/{$totalPages}: Cached " . count($devices) . " devices");

            // Move to next page or next phase
            if ($page >= $totalPages) {
                $state['status'] = 'fetching_drilldowns';
                $state['drilldown_index'] = 0;
                logMessage("Device fetch complete. Starting drill-down fetch for " . count($state['devices_to_fetch_drilldown']) . " devices");
            } else {
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
        $devicesToFetch = $state['devices_to_fetch_drilldown'];
        $index = $state['drilldown_index'];
        $chunkSize = 10; // Fetch 10 drill-downs per request

        if ($index >= count($devicesToFetch)) {
            // All drill-downs complete - move to cutover
            $state['status'] = 'ready_for_cutover';
            logMessage("Drill-down fetch complete. Ready for atomic cutover.");
        } else {
            $chunk = array_slice($devicesToFetch, $index, $chunkSize);
            logMessage("Fetching drill-downs {$index}-" . ($index + count($chunk)) . "/" . count($devicesToFetch));

            foreach ($chunk as $serial) {
                try {
                    $drilldown = callMPSAPI('Device/Get', ['SerialNumber' => $serial]);

                    if ($drilldown && isset($drilldown['serial'])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO {$prefix}cache_device_drilldown_staging
                            ({$drilldownSerialColumn}, drilldown_data, has_alerts, has_supplies, cached_at)
                            VALUES (:serial, :data, :has_alerts, :has_supplies, NOW())
                            ON DUPLICATE KEY UPDATE
                                drilldown_data = VALUES(drilldown_data),
                                has_alerts = VALUES(has_alerts),
                                has_supplies = VALUES(has_supplies),
                                cached_at = VALUES(cached_at)
                        ");

                        $hasSupplyAlerts = isset($drilldown['supplyAlerts']) && count($drilldown['supplyAlerts']) > 0;
                        $hasSupplies = isset($drilldown['supplies']) && count($drilldown['supplies']) > 0;

                        $stmt->execute([
                            ':serial' => $serial,
                            ':data' => json_encode($drilldown, JSON_UNESCAPED_UNICODE),
                            ':has_alerts' => $hasSupplyAlerts ? 1 : 0,
                            ':has_supplies' => $hasSupplies ? 1 : 0
                        ]);

                        $state['drilldowns_cached']++;
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
    }

    // PHASE 3: Atomic cutover
    elseif ($state['status'] === 'ready_for_cutover') {
        logMessage("Performing atomic table cutover");

        try {
            // Rename tables atomically
            $pdo->exec("RENAME TABLE
                {$prefix}cache_devices TO {$prefix}cache_devices_old,
                {$prefix}cache_devices_staging TO {$prefix}cache_devices,
                {$prefix}cache_device_drilldown TO {$prefix}cache_device_drilldown_old,
                {$prefix}cache_device_drilldown_staging TO {$prefix}cache_device_drilldown
            ");

            // Drop old tables
            $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_devices_old");
            $pdo->exec("DROP TABLE IF EXISTS {$prefix}cache_device_drilldown_old");

            $state['status'] = 'completed';
            $state['completed_at'] = date('Y-m-d H:i:s');

            $duration = strtotime($state['completed_at']) - strtotime($state['started_at']);
            logMessage("=== REFRESH COMPLETE ===");
            logMessage("Devices cached: {$state['devices_cached']}");
            logMessage("Drill-downs cached: {$state['drilldowns_cached']}");
            logMessage("Duration: {$duration} seconds");
            logMessage("Errors: " . count($state['errors']));

        } catch (Exception $e) {
            $error = "Cutover failed: " . $e->getMessage();
            logMessage("CRITICAL ERROR: {$error}");
            $state['status'] = 'cutover_failed';
            $state['errors'][] = $error;
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
        'continue' => !in_array($state['status'], ['completed', 'cutover_failed'])
    ]);
}

// ==================================================================
// ACTION: STATUS - Check current progress
// ==================================================================
if ($action === 'status') {
    $state = getState();

    if (!$state) {
        respondJson([
            'success' => true,
            'status' => 'idle',
            'message' => 'No refresh in progress'
        ]);
    }

    respondJson([
        'success' => true,
        'state' => $state
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
        'status' => '?action=status - Check progress',
        'auto' => '?action=auto - Auto-process (same as process)'
    ]
]);

/*
CHANGELOG
2025-11-18 Codex
- Fixed the staging INSERTs so they reference `serial_number`/`drilldown_data` (matching the actual cache schemas) instead of the obsolete `device_serial` columns, eliminating the SQLSTATE 42S22 cron error.
2025-11-19 Codex
- Added a `version` payload (constant `REFRESH_CACHE_CHUNKED_VERSION`) so cron emails can prove the deployed script is the updated one, plus logged the version in every JSON response.
- Added runtime detection of `serial_number` vs `device_serial` columns (plus state persistence) so the chunked refresh works even if the target cache table still uses the legacy column name.
2025-11-14 Codex
- Hardened CLI detection so cron executions running via cgi-fcgi wrappers skip HTTP headers and return pure JSON to the router.
*/
