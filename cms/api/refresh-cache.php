<?php
/**
 * Cache Refresh Endpoint
 * Called by users OR cron to refresh device cache
 * Uses existing CMS API endpoints that handle auth automatically
 */

require '../config.php';
require '../functions.php';

// Require authentication (starts session automatically)
requireAuth();

$cacheFile = __DIR__ . '/cache/device-cache.json';
$lockFile = __DIR__ . '/cache/refresh.lock';

// Prevent concurrent refreshes
if (file_exists($lockFile)) {
    $lockAge = time() - filemtime($lockFile);
    if ($lockAge < 300) {
        die(json_encode(['success' => true, 'status' => 'skipped', 'reason' => 'refresh in progress']));
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

    // Fetch installed devices via CMS API (handles auth)
    $installedPages = 0;
    for ($page = 1; $page <= 50; $page++) {
        $url = "http://{$_SERVER['HTTP_HOST']}/cms/api/get-devices.php?pageRows=200&pageNumber={$page}&allCustomers=true";

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Cookie: " . ($_SERVER['HTTP_COOKIE'] ?? ''),
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) {
            error_log("[CACHE] Failed to fetch installed devices page {$page}");
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !$data['success'] || empty($data['devices'])) {
            break;
        }

        $allDevices = array_merge($allDevices, $data['devices']);
        $installedPages = $page;

        error_log("[CACHE] Fetched installed page {$page}: " . count($data['devices']) . " devices");

        if (count($data['devices']) < 200) {
            break;
        }
    }

    error_log("[CACHE] Total installed devices: " . count($allDevices) . " from {$installedPages} pages");

    // Fetch deleted/uninstalled devices
    $deletedPages = 0;
    for ($page = 1; $page <= 20; $page++) {
        $url = "http://{$_SERVER['HTTP_HOST']}/cms/api/get-deleted-devices.php?pageRows=200&pageNumber={$page}&dealerCode=" . DEFAULT_DEALER_CODE;

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Cookie: " . ($_SERVER['HTTP_COOKIE'] ?? ''),
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) {
            error_log("[CACHE] Failed to fetch deleted devices page {$page}");
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !$data['success'] || empty($data['devices'])) {
            break;
        }

        // Mark as uninstalled
        foreach ($data['devices'] as &$device) {
            $device['IsUninstalled'] = true;
        }

        $allDevices = array_merge($allDevices, $data['devices']);
        $deletedPages = $page;

        error_log("[CACHE] Fetched deleted page {$page}: " . count($data['devices']) . " devices");

        if (count($data['devices']) < 200) {
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
        'expires' => time() + 300, // 5 minutes
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
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("[CACHE] Error: " . $e->getMessage());
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
    jsonError($e->getMessage());
}
