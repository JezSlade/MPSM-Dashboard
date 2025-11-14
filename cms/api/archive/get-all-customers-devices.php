<?php
/**
 * Get ALL Devices Across ALL Customers
 * Queries each customer individually and combines results
 */

require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$dealerId = $_GET['dealerId'] ?? DEFAULT_DEALER_ID;

try {
    // First, get list of all customers for this dealer
    $payload = json_encode([
        'action' => 'Customer/List',
        'params' => [
            'FilterDealerCodes' => [$dealerCode],
            'PageNumber' => 1,
            'PageRows' => 1000 // Get all customers
        ]
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
    $data = json_decode($response, true);

    if (!$data || !$data['success']) {
        throw new Exception("Failed to get customer list");
    }

    $raw = $data['data'] ?? [];
    $customers = $raw['Items'] ?? $raw['Result'] ?? [];

    error_log("[ALL-DEVICES] Found " . count($customers) . " customers");

    // Now query devices for each customer
    $allDevices = [];
    $seenDevices = [];

    foreach ($customers as $customer) {
        $customerCode = $customer['Code'] ?? $customer['CustomerCode'] ?? '';

        if (!$customerCode) {
            continue;
        }

        error_log("[ALL-DEVICES] Querying customer: {$customerCode}");

        // Query devices for this customer (paginated)
        for ($page = 1; $page <= 50; $page++) {
            $payload = json_encode([
                'action' => 'Device/List',
                'params' => [
                    'FilterDealerCodes' => [$dealerCode],
                    'FilterCustomerCodes' => [$customerCode],
                    'PageNumber' => $page,
                    'PageRows' => 200,
                    'SortColumn' => 'AssetNumber',
                    'SortOrder' => 'Asc'
                ]
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
            $data = json_decode($response, true);

            if (!$data || !$data['success']) {
                break;
            }

            $raw = $data['data'] ?? [];
            $devices = $raw['Items'] ?? $raw['Result'] ?? [];

            if (empty($devices)) {
                break;
            }

            // Add devices, avoiding duplicates
            foreach ($devices as $device) {
                $deviceId = $device['Id'] ?? $device['DeviceId'] ?? $device['SerialNumber'] ?? '';
                if ($deviceId && !isset($seenDevices[$deviceId])) {
                    $seenDevices[$deviceId] = true;
                    $allDevices[] = $device;
                }
            }

            if (count($devices) < 200) {
                break;
            }
        }
    }

    error_log("[ALL-DEVICES] Total unique devices: " . count($allDevices));

    jsonSuccess([
        'devices' => $allDevices,
        'total' => count($allDevices),
        'customers_queried' => count($customers)
    ]);

} catch (Exception $e) {
    error_log("[ALL-DEVICES] Error: " . $e->getMessage());
    jsonError($e->getMessage());
}
