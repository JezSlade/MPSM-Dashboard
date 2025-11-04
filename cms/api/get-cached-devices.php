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

// Not in cache, fetch fresh data
try {
    // Step 1: Get all customers for this dealer
    $customers = [];
    try {
        $customersData = callMpsApi('Customer/List', [
            'DealerCode' => DEFAULT_DEALER_CODE,
            'SortColumn' => 'CustomerName',
            'SortOrder' => 'Asc'
        ]);

        if (isset($customersData['Items'])) {
            $customers = $customersData['Items'];
        } elseif (isset($customersData['Result'])) {
            $customers = $customersData['Result'];
        }
    } catch (Exception $e) {
        // If customer list fails, try to continue with empty list
        error_log("Failed to fetch customers: " . $e->getMessage());
    }

    // Step 2: Fetch devices for each customer
    $allDevices = [];
    $processedCustomers = 0;

    foreach ($customers as $customer) {
        $customerId = $customer['Id'] ?? $customer['CustomerId'] ?? null;
        if (!$customerId) {
            continue;
        }

        $customerDevices = [];
        $pageNumber = 1;
        $maxPages = 20; // Limit pages per customer

        // Fetch all pages for this customer
        while ($pageNumber <= $maxPages) {
            try {
                $params = [
                    'FilterCustomerIds' => [$customerId],
                    'PageNumber' => $pageNumber,
                    'PageRows' => 200,
                    'SortColumn' => 'AssetNumber',
                    'SortOrder' => 'Asc'
                ];

                $deviceData = callMpsApi('Device/List', $params);

                $pageDevices = [];
                if (isset($deviceData['Items']) && is_array($deviceData['Items'])) {
                    $pageDevices = $deviceData['Items'];
                } elseif (isset($deviceData['Result']) && is_array($deviceData['Result'])) {
                    $pageDevices = $deviceData['Result'];
                }

                if (empty($pageDevices)) {
                    break;
                }

                // Add customer description to each device
                foreach ($pageDevices as &$device) {
                    $device['CustomerDescription'] = $customer['CustomerName'] ?? $customer['Description'] ?? 'Unknown';
                    $device['CustomerId'] = $customerId;
                }
                unset($device);

                $customerDevices = array_merge($customerDevices, $pageDevices);

                // If we got less than 200, we're done with this customer
                if (count($pageDevices) < 200) {
                    break;
                }

                $pageNumber++;

            } catch (Exception $e) {
                error_log("Failed to fetch devices for customer {$customerId}: " . $e->getMessage());
                break;
            }
        }

        $allDevices = array_merge($allDevices, $customerDevices);
        $processedCustomers++;
    }

    // Step 3: Fetch deleted/uninstalled devices for the dealer
    $deletedDevices = [];
    $deletedPageNumber = 1;
    $maxDeletedPages = 10;

    while ($deletedPageNumber <= $maxDeletedPages) {
        try {
            $deletedData = callMpsApi('Device/Deleted/ListByDealer', [
                'DealerCode' => DEFAULT_DEALER_CODE,
                'PageNumber' => $deletedPageNumber,
                'PageRows' => 200,
                'SortColumn' => 'AssetNumber',
                'SortOrder' => 'Asc'
            ]);

            $pageDevices = [];
            if (isset($deletedData['Items']) && is_array($deletedData['Items'])) {
                $pageDevices = $deletedData['Items'];
            } elseif (isset($deletedData['Result']) && is_array($deletedData['Result'])) {
                $pageDevices = $deletedData['Result'];
            }

            if (empty($pageDevices)) {
                break;
            }

            // Mark as uninstalled and add customer description
            foreach ($pageDevices as &$device) {
                $device['IsUninstalled'] = true;
                // Try to find customer name from existing data
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

        } catch (Exception $e) {
            error_log("Failed to fetch deleted devices: " . $e->getMessage());
            break;
        }
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
