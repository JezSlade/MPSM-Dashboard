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
        'customers' => $cached['customers'],
        'processed_customers' => $cached['processed_customers'] ?? 0,
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
    // Step 1: Get all customers for this dealer
    $customers = [];
    $customersData = callMpsApiDirect('Customer/List', [
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'PageNumber' => 1,
        'PageRows' => 1000
    ]);

    // Customer/List returns wrapped data
    if ($customersData && is_array($customersData)) {
        // Try Items first, then Result, then direct array
        if (isset($customersData['Items']) && is_array($customersData['Items'])) {
            $customers = $customersData['Items'];
        } elseif (isset($customersData['Result']) && is_array($customersData['Result'])) {
            $customers = $customersData['Result'];
        } else {
            $customers = $customersData;
        }
    }

    // Step 2: Fetch devices for each customer
    $allDevices = [];
    $processedCustomers = 0;

    foreach ($customers as $customer) {
        $customerCode = $customer['Code'] ?? $customer['CustomerCode'] ?? null;
        $customerName = $customer['Description'] ?? $customer['CustomerName'] ?? 'Unknown';

        if (!$customerCode) {
            continue;
        }

        $pageNumber = 1;
        $maxPages = 20; // Limit pages per customer

        // Fetch all pages for this customer
        while ($pageNumber <= $maxPages) {
            $deviceData = callMpsApiDirect('Device/List', [
                'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
                'FilterCustomerCodes' => [$customerCode],
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

            // Add customer description and code to each device
            foreach ($pageDevices as &$device) {
                $device['CustomerDescription'] = $customerName;
                $device['CustomerCode'] = $customerCode;
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
        'customers' => count($customers),
        'processed_customers' => $processedCustomers,
        'deleted_devices' => count($deletedDevices),
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
