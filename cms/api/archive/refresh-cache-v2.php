<?php
/**
 * Cache Refresh Endpoint V2
 * Calls API functions directly instead of HTTP
 */

require '../config.php';
require '../functions.php';

requireAuth();

$cacheFile = __DIR__ . '/cache/device-cache.json';
$lockFile = __DIR__ . '/cache/refresh.lock';

// Prevent concurrent refreshes
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge < 300) {
        jsonSuccess(['status' => 'skipped', 'reason' => 'refresh in progress']);
        exit;
    }
    unlink($lockFile);
}

// Create lock
$cacheDir = dirname($cacheFile);
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
file_put_contents($lockFile, time());

try {
    error_log("[CACHE] Starting device cache refresh");
    $startTime = microtime(true);

    $allDevices = [];
    $dealerCode = DEFAULT_DEALER_CODE;

    // Fetch installed devices directly via mps-api
    $installedPages = 0;
    for ($page = 1; $page <= 50; $page++) {
        $params = [
            'FilterDealerCodes' => [$dealerCode],
            'PageNumber' => $page,
            'PageRows' => 200,
            'SortColumn' => 'AssetNumber',
            'SortOrder' => 'Asc'
        ];

        $payload = json_encode([
            'action' => 'Device/List',
            'params' => $params
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
        if (!$response) {
            error_log("[CACHE] Failed to fetch installed devices page {$page}");
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['success']) || !$data['success']) {
            error_log("[CACHE] Invalid response on page {$page}");
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
            error_log("[CACHE] No devices on page {$page}, stopping");
            break;
        }

        $allDevices = array_merge($allDevices, $pageDevices);
        $installedPages = $page;

        error_log("[CACHE] Fetched installed page {$page}: " . count($pageDevices) . " devices (total: " . count($allDevices) . ")");

        if (count($pageDevices) < 200) {
            break;
        }
    }

    error_log("[CACHE] Total installed devices: " . count($allDevices) . " from {$installedPages} pages");

    // Fetch deleted/uninstalled devices
    $deletedPages = 0;
    for ($page = 1; $page <= 20; $page++) {
        $params = [
            'dealerCode' => $dealerCode,
            'pageNumber' => $page,
            'pageRows' => 200,
            'sortColumn' => 'AssetNumber',
            'sortOrder' => 'Asc'
        ];

        $payload = json_encode([
            'action' => 'Device/Deleted/List',
            'params' => $params
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
        if (!$response) {
            error_log("[CACHE] Failed to fetch deleted devices page {$page}");
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

        $allDevices = array_merge($allDevices, $pageDevices);
        $deletedPages = $page;

        error_log("[CACHE] Fetched deleted page {$page}: " . count($pageDevices) . " devices");

        if (count($pageDevices) < 200) {
            break;
        }
    }

    $totalDevices = count($allDevices);
    $duration = round(microtime(true) - $startTime, 2);

    error_log("[CACHE] Total devices: {$totalDevices} ({$installedPages} installed pages + {$deletedPages} deleted pages) in {$duration}s");

    // Save to cache
    $cacheData = [
        'devices' => $allDevices,
        'timestamp' => time(),
        'expires' => time() + 300,
        'total' => $totalDevices,
        'meta' => [
            'installed_pages' => $installedPages,
            'deleted_pages' => $deletedPages,
            'duration' => $duration
        ]
    ];

    file_put_contents($cacheFile, json_encode($cacheData));
    error_log("[CACHE] Cache saved: {$totalDevices} devices");

    // Remove lock
    unlink($lockFile);

    jsonSuccess([
        'status' => 'refreshed',
        'devices' => $totalDevices,
        'duration' => $duration,
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'installed_pages' => $installedPages,
            'deleted_pages' => $deletedPages
        ]
    ]);

} catch (Exception $e) {
    error_log("[CACHE] Error: " . $e->getMessage());
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    jsonError($e->getMessage());
}
