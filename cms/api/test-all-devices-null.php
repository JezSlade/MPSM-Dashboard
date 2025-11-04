<?php
// Test Device/List with FilterCustomerCodes=null for ALL dealer devices
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== Testing Device/List with FilterCustomerCodes=null ===\n\n";

$totalDevices = 0;

for ($page = 1; $page <= 20; $page++) {
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => null,  // ← KEY: null = all customers
            'ProductBrand' => null,
            'ProductModel' => null,
            'OfficeId' => null,
            'Status' => 1,
            'FilterText' => null,
            'PageNumber' => $page,
            'PageRows' => 200,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ]
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
        echo "Page $page: Failed to fetch\n";
        break;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
        echo "Page $page: Error - " . ($data['error'] ?? 'Unknown') . "\n";
        break;
    }

    $raw = $data['data'] ?? [];
    $devices = $raw['Items'] ?? $raw['Result'] ?? $raw;

    if (empty($devices)) {
        echo "Page $page: No devices (stopping)\n";
        break;
    }

    echo "Page $page: " . count($devices) . " devices\n";
    $totalDevices += count($devices);

    // Search for FQ966
    foreach ($devices as $device) {
        $json_str = json_encode($device);
        if (stripos($json_str, 'FQ966') !== false) {
            echo "\n*** FOUND FQ966 ON PAGE $page! ***\n";
            echo "ExternalIdentifier: " . ($device['ExternalIdentifier'] ?? 'N/A') . "\n";
            echo "AssetNumber: " . ($device['AssetNumber'] ?? 'N/A') . "\n";
            echo "SerialNumber: " . ($device['SerialNumber'] ?? 'N/A') . "\n";
            echo "Model: " . ($device['Product']['Model'] ?? 'N/A') . "\n";
            echo "Customer: " . ($device['CustomerDescription'] ?? 'N/A') . "\n\n";
        }
    }

    if (count($devices) < 200) {
        echo "Last page reached\n";
        break;
    }
}

echo "\n=== RESULTS ===\n";
echo "Total devices: $totalDevices\n";
echo "Expected: 3000+\n";
