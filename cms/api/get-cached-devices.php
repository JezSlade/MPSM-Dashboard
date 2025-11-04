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

$cacheKey = 'all-devices-dealer-' . DEFAULT_DEALER_CODE;
$cacheTTL = 300; // 5 minutes

// Try to get from cache first
$cached = cacheGet($cacheKey);
if ($cached !== null) {
    jsonSuccess([
        'devices' => $cached['devices'],
        'total' => $cached['total'],
        'customers' => $cached['customers'],
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
    // Step 1: Get all customers for this dealer
    $customers = [];
    $customersData = callMpsApiDirect('Customer/GetCustomers', [
        'DealerCode' => DEFAULT_DEALER_CODE,
        'PageNumber' => 1,
        'PageRows' => 500,
        'SortColumn' => 'Description',
        'SortOrder' => 'Asc'
    ]);

    // Customer/GetCustomers returns array directly, not wrapped
    if ($customersData && is_array($customersData)) {
        $customers = $customersData;
    }

    // Step 2: Fetch devices for each customer
    $allDevices = [];
    $processedCustomers = 0;

    foreach ($customers as $customer) {
        $customerId = $customer['Id'] ?? $customer['CustomerId'] ?? null;
        $customerName = $customer['Description'] ?? $customer['CustomerName'] ?? 'Unknown';

        if (!$customerId) {
            continue;
        }

        $pageNumber = 1;
        $maxPages = 20; // Limit pages per customer

        // Fetch all pages for this customer
        while ($pageNumber <= $maxPages) {
            $deviceData = callMpsApiDirect('Device/List', [
                'FilterCustomerIds' => [$customerId],
                'PageNumber' => $pageNumber,
                'PageRows' => 200,
                'SortColumn' => 'AssetNumber',
                'SortOrder' => 'Asc'
            ]);

            if (!$deviceData || !is_array($deviceData)) {
                break;
            }

            // Device/List returns array directly
            $pageDevices = $deviceData;

            if (empty($pageDevices)) {
                break;
            }

            // Add customer description to each device
            foreach ($pageDevices as &$device) {
                $device['CustomerDescription'] = $customerName;
                $device['CustomerId'] = $customerId;
            }
            unset($device);

            $allDevices = array_merge($allDevices, $pageDevices);

            // If we got less than 200, we're done with this customer
            if (count($pageDevices) < 200) {
                break;
            }

            $pageNumber++;
        }

        $processedCustomers++;
    }

    // Step 3: Fetch deleted/uninstalled devices for the dealer
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

        // Device/Deleted/ListByDealer returns array directly
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
        'customers' => count($customers),
        'timestamp' => time()
    ];

    // Store in cache
    cacheStore($cacheKey, $cacheData, $cacheTTL);

    // Return success
    jsonSuccess([
        'devices' => $allDevices,
        'total' => count($allDevices),
        'customers' => count($customers),
        'processed_customers' => $processedCustomers,
        'deleted_devices' => count($deletedDevices),
        'cached' => false,
        'refreshed' => true
    ]);

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
