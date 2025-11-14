<?php
require '../config.php';
require '../functions.php';
requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

try {
    // Step 1: Get all customers
    $custPayload = json_encode([
        'action' => 'Customer/GetCustomers',
        'params' => ['DealerCode' => $dealerCode, 'PageNumber' => 1, 'PageRows' => 500]
    ]);

    $context = stream_context_create([
        'http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => $custPayload, 'timeout' => 15, 'ignore_errors' => true]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    $custData = json_decode($response, true);
    $customers = $custData['data']['Items'] ?? [];

    // Step 2: Query first page of devices for each customer
    $allDevices = [];
    $seenIds = [];

    foreach ($customers as $customer) {
        $custCode = $customer['Code'];

        // Get installed devices
        $devPayload = json_encode([
            'action' => 'Device/List',
            'params' => ['FilterDealerCodes' => [$dealerCode], 'FilterCustomerCodes' => [$custCode], 'PageNumber' => 1, 'PageRows' => 100, 'SortColumn' => 'AssetNumber', 'SortOrder' => 'Asc']
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => $devPayload, 'timeout' => 10, 'ignore_errors' => true]]));
        if ($response) {
            $devData = json_decode($response, true);
            $devices = $devData['data']['Items'] ?? $devData['data'] ?? [];
            foreach ($devices as $device) {
                $id = $device['Id'] ?? $device['SerialNumber'] ?? uniqid();
                if (!in_array($id, $seenIds)) {
                    $seenIds[] = $id;
                    $allDevices[] = $device;
                }
            }
        }
    }

    jsonSuccess(['devices' => $allDevices, 'total' => count($allDevices), 'customers' => count($customers)]);

} catch (Exception $e) {
    jsonError("Failed: " . $e->getMessage());
}
