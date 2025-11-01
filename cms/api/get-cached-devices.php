<?php
/**
 * Get Cached Devices API
 * Returns pre-cached device data that is refreshed every 5 minutes in background
 * This ensures all users get instant access to fresh data without waiting
 */

require '../config.php';
require '../functions.php';

requireAuth();

$cacheFile = __DIR__ . '/cache/device-cache.json';
$cacheLifetime = 300; // 5 minutes

// Ensure cache directory exists
$cacheDir = dirname($cacheFile);
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Function to refresh cache
function refreshDeviceCache($cacheFile, $dealerCode) {
    // Fetch installed devices
    $installedParams = [
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    $devices = [];
    $pageNumber = 1;
    $maxPages = 50;

    // Fetch all installed devices
    while ($pageNumber <= $maxPages) {
        $installedParams['PageNumber'] = $pageNumber;
        $installedParams['PageRows'] = 200;

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

    // Fetch deleted/uninstalled devices using ListByDealer
    $deletedParams = [
        'DealerCode' => $dealerCode,
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    $deletedPageNumber = 1;
    while ($deletedPageNumber <= 20) {
        $deletedParams['PageNumber'] = $deletedPageNumber;

        $payload = json_encode([
            'action' => 'Device/Deleted/ListByDealer',
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

    // Save to cache
    $cacheData = [
        'devices' => $devices,
        'timestamp' => time(),
        'expires' => time() + 300,
        'total' => count($devices)
    ];

    file_put_contents($cacheFile, json_encode($cacheData));
    return $cacheData;
}

try {
    $dealerCode = DEFAULT_DEALER_CODE;

    // Check if cache exists and is fresh
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);

        if ($cacheData && isset($cacheData['expires']) && $cacheData['expires'] > time()) {
            // Cache is fresh, return it
            jsonSuccess([
                'devices' => $cacheData['devices'],
                'total' => $cacheData['total'],
                'cached' => true,
                'age' => time() - $cacheData['timestamp']
            ]);
            exit;
        }
    }

    // Cache is stale or doesn't exist, refresh it
    $cacheData = refreshDeviceCache($cacheFile, $dealerCode);

    jsonSuccess([
        'devices' => $cacheData['devices'],
        'total' => $cacheData['total'],
        'cached' => false,
        'refreshed' => true
    ]);

} catch (Exception $e) {
    jsonError("Failed to fetch cached devices: " . $e->getMessage());
}
