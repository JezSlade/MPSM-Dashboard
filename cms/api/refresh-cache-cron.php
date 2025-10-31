<?php
/**
 * Background Cache Refresh Cron Job
 * Run this every 5 minutes to keep device cache fresh
 *
 * Setup:
 * Add to crontab: *//*5 * * * * curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-cron.php
 * Or use Windows Task Scheduler to run every 5 minutes
 */

require '../config.php';

$cacheFile = __DIR__ . '/cache/device-cache.json';
$lockFile = __DIR__ . '/cache/refresh.lock';

// Prevent concurrent refreshes
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge < 300) { // 5 minutes
        die(json_encode(['status' => 'skipped', 'reason' => 'refresh in progress']));
    }
    // Lock is stale, remove it
    unlink($lockFile);
}

// Create lock
file_put_contents($lockFile, time());

try {
    $dealerCode = DEFAULT_DEALER_CODE;
    $devices = [];

    // Fetch installed devices
    error_log("[CACHE] Starting device cache refresh");

    $installedParams = [
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    $pageNumber = 1;
    $maxPages = 50;

    while ($pageNumber <= $maxPages) {
        $installedParams['PageNumber'] = $pageNumber;

        $payload = json_encode([
            'action' => 'Device/List',
            'params' => $installedParams
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
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['success']) || !$data['success']) {
            break;
        }

        $raw = $data['data'] ?? [];
        $pageDevices = [];

        if (isset($raw['Items']) && is_array($raw['Items'])) {
            $pageDevices = $raw['Items'];
        } elseif (isset($raw['Result']) && is_array($raw['Result'])) {
            $pageDevices = $raw['Result'];
        }

        if (empty($pageDevices)) {
            break;
        }

        $devices = array_merge($devices, $pageDevices);

        if (count($pageDevices) < 200) {
            break;
        }

        $pageNumber++;
    }

    error_log("[CACHE] Fetched " . count($devices) . " installed devices");

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

        $payload = json_encode([
            'action' => 'Device/Deleted/List',
            'params' => $deletedParams
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
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['success']) || !$data['success']) {
            break;
        }

        $raw = $data['data'] ?? [];
        $pageDevices = [];

        if (isset($raw['Items']) && is_array($raw['Items'])) {
            $pageDevices = $raw['Items'];
        } elseif (isset($raw['Result']) && is_array($raw['Result'])) {
            $pageDevices = $raw['Result'];
        }

        if (empty($pageDevices)) {
            break;
        }

        // Mark as uninstalled
        foreach ($pageDevices as &$device) {
            $device['IsUninstalled'] = true;
        }

        $devices = array_merge($devices, $pageDevices);

        if (count($pageDevices) < 200) {
            break;
        }

        $deletedPageNumber++;
    }

    error_log("[CACHE] Total devices (installed + uninstalled): " . count($devices));

    // Save to cache
    $cacheData = [
        'devices' => $devices,
        'timestamp' => time(),
        'expires' => time() + 300,
        'total' => count($devices)
    ];

    // Ensure cache directory exists
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    file_put_contents($cacheFile, json_encode($cacheData));
    error_log("[CACHE] Cache refreshed successfully");

    // Remove lock
    unlink($lockFile);

    die(json_encode([
        'status' => 'success',
        'devices' => count($devices),
        'timestamp' => date('Y-m-d H:i:s')
    ]));

} catch (Exception $e) {
    error_log("[CACHE] Error: " . $e->getMessage());
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}
