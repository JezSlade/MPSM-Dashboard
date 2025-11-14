<?php
/**
 * Chunked Cache Refresh System
 *
 * Designed to work within web server timeout constraints by processing
 * in small chunks that complete in <60 seconds each.
 *
 * ARCHITECTURE:
 * - Uses staging tables (_staging suffix)
 * - Tracks progress in state file
 * - Each request processes one chunk and exits
 * - Dashboard/CRON repeatedly calls until complete
 * - Atomic cutover when all chunks complete
 *
 * USAGE:
 * 1. Start: curl "...?action=start"
 * 2. Process: curl "...?action=process" (repeat until done)
 * 3. Status: curl "...?action=status"
 *
 * AUTO-MODE: curl "...?action=auto" processes one chunk automatically
 */

set_time_limit(120); // 2 minutes max per chunk
ini_set('memory_limit', '512M');

require '../config.php';
require '../functions.php';
require_once dirname(__DIR__, 2) . '/bootstrap.php';

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
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// ==================================================================
// ACTION: START - Initialize new refresh cycle
// ==================================================================
if ($_GET['action'] === 'start') {
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

    saveState($state);
    logMessage("State initialized - ready to process");

    respondJson([
        'success' => true,
        'action' => 'start',
        'message' => 'Refresh initialized. Call ?action=process to begin.',
        'state' => $state
    ]);
}

// ==================================================================
// ACTION: PROCESS - Process next chunk
// ==================================================================
if ($_GET['action'] === 'process' || $_GET['action'] === 'auto') {
    $state = getState();

    if (!$state) {
        respondJson([
            'success' => false,
            'error' => 'No active refresh. Call ?action=start first.'
        ]);
    }

    $pdo = getDatabase();
    $prefix = DB_PREFIX;
    $chunkStartTime = microtime(true);

    // PHASE 1: Fetch device list pages
    if ($state['status'] === 'fetching_devices') {
        $page = $state['current_page'];
        $perPage = 100;

        logMessage("Fetching device list page {$page}");

        try {
            $response = callMpsGetDeviceList($page, $perPage, true, true);

            if (!$response || !isset($response['data'])) {
                throw new Exception("Invalid API response for page {$page}");
            }

            $devices = $response['data'];
            $totalPages = $response['pagination']['total_pages'] ?? 1;

            if ($state['total_pages'] === null) {
                $state['total_pages'] = $totalPages;
                logMessage("Total pages detected: {$totalPages}");
            }

            // Cache devices to staging table
            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}cache_devices_staging
                (device_serial, customer_code, device_type, install_status, device_data, cached_at)
                VALUES (:serial, :customer, :type, :status, :data, NOW())
                ON DUPLICATE KEY UPDATE
                    customer_code = VALUES(customer_code),
                    device_type = VALUES(device_type),
                    install_status = VALUES(install_status),
                    device_data = VALUES(device_data),
                    cached_at = VALUES(cached_at)
            ");

            foreach ($devices as $device) {
                $serial = $device['serial'] ?? $device['deviceSerial'] ?? null;
                if (!$serial) continue;

                $stmt->execute([
                    ':serial' => $serial,
                    ':customer' => $device['customerCode'] ?? $device['customer_code'] ?? null,
                    ':type' => $device['deviceType'] ?? $device['device_type'] ?? 'unknown',
                    ':status' => $device['installStatus'] ?? $device['install_status'] ?? 'unknown',
                    ':data' => json_encode($device, JSON_UNESCAPED_UNICODE)
                ]);

                $state['devices_cached']++;

                // Queue for drill-down fetch (only installed devices)
                $installStatus = strtolower($device['installStatus'] ?? $device['install_status'] ?? '');
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
                    $drilldown = callMpsGetDeviceBySerial($serial);

                    if ($drilldown && isset($drilldown['serial'])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO {$prefix}cache_device_drilldown_staging
                            (device_serial, drilldown_data, has_supply_alerts, has_supplies, cached_at)
                            VALUES (:serial, :data, :has_alerts, :has_supplies, NOW())
                            ON DUPLICATE KEY UPDATE
                                drilldown_data = VALUES(drilldown_data),
                                has_supply_alerts = VALUES(has_supply_alerts),
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
if ($_GET['action'] === 'status') {
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
