<?php
/**
 * Get Cached Devices API - All Customers for Dealer
 * Returns pre-cached device data from all customers for the dealer
 * Cached using the cache engine for 5 minutes
 * Searches across all customers to ensure comprehensive device discovery
 */

require '../config.php';
require '../functions.php';

requireAuth();

// Increase limits for comprehensive device fetching across all customers
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

$cacheKey = 'all-devices-dealer-' . DEFAULT_DEALER_CODE;
$cacheTTL = 300; // 5 minutes

// Try to get from cache first
$cached = cacheGet($cacheKey);
if ($cached !== null) {
    jsonSuccess([
        'devices' => $cached['devices'],
        'total' => $cached['total'],
        'installed_devices' => $cached['installed_devices'] ?? 0,
        'deleted_devices' => $cached['deleted_devices'] ?? 0,
        'cached' => true,
        'age' => time() - $cached['timestamp']
    ]);
    exit;
}

// Helper function to call MPS API
function callMpsApiDirect($action, $params) {
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

    $data = json_decode($response, true);

    if (!$data || !isset($data['success']) || !$data['success']) {
        return null;
    }

    return $data['data'] ?? [];
}

// Not in cache, fetch fresh data
try {
    // Step 1: Fetch all installed devices for this dealer (using FilterDealerId like get-devices.php)
    $allDevices = [];
    $pageNumber = 1;
    $maxPages = 50; // Safety limit

    while ($pageNumber <= $maxPages) {
        $deviceData = callMpsApiDirect('Device/List', [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
            'PageNumber' => $pageNumber,
            'PageRows' => 200,
            'SortColumn' => 'AssetNumber',
            'SortOrder' => 'Asc'
        ]);

        if (!$deviceData || !is_array($deviceData)) {
            break;
        }

        // Device/List returns wrapped data - try Items, Result, or direct array
        $pageDevices = [];
        if (isset($deviceData['Items']) && is_array($deviceData['Items'])) {
            $pageDevices = $deviceData['Items'];
        } elseif (isset($deviceData['Result']) && is_array($deviceData['Result'])) {
            $pageDevices = $deviceData['Result'];
        } elseif (is_array($deviceData)) {
            $pageDevices = $deviceData;
        }

        if (empty($pageDevices)) {
            break;
        }

        $allDevices = array_merge($allDevices, $pageDevices);

        // If we got less than 200, we're done
        if (count($pageDevices) < 200) {
            break;
        }

        $pageNumber++;
    }

    // Step 2: Fetch deleted/uninstalled devices for the dealer
    $deletedDevices = [];
    $deletedPageNumber = 1;
    $maxDeletedPages = 10;

    while ($deletedPageNumber <= $maxDeletedPages) {
        $deletedData = callMpsApiDirect('Device/Deleted/ListByDealer', [
            'DealerCode' => DEFAULT_DEALER_CODE,
            'PageNumber' => $deletedPageNumber,
            'PageRows' => 200,
            'SortColumn' => 'AssetNumber',
            'SortOrder' => 'Asc'
        ]);

        if (!$deletedData || !is_array($deletedData)) {
            break;
        }

        // Device/Deleted/ListByDealer returns direct array (tested and confirmed)
        $pageDevices = $deletedData;

        if (empty($pageDevices)) {
            break;
        }

        // Mark as uninstalled and add customer description
        foreach ($pageDevices as &$device) {
            $device['IsUninstalled'] = true;
            if (!isset($device['CustomerDescription'])) {
                $device['CustomerDescription'] = $device['CustomerName'] ?? 'Unknown';
            }
        }
        unset($device);

        $deletedDevices = array_merge($deletedDevices, $pageDevices);

        if (count($pageDevices) < 200) {
            break;
        }

        $deletedPageNumber++;
    }

    // Combine all devices
    $allDevices = array_merge($allDevices, $deletedDevices);

    // Prepare cache data
    $cacheData = [
        'devices' => $allDevices,
        'total' => count($allDevices),
        'installed_devices' => count($allDevices) - count($deletedDevices),
        'deleted_devices' => count($deletedDevices),
        'timestamp' => time()
    ];

    // Store in cache
    cacheStore($cacheKey, $cacheData, $cacheTTL);

    // Return success
    jsonSuccess([
        'devices' => $allDevices,
        'total' => count($allDevices),
        'installed_devices' => count($allDevices) - count($deletedDevices),
        'deleted_devices' => count($deletedDevices),
        'cached' => false,
        'refreshed' => true
    ]);

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
