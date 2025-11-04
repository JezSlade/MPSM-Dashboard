<?php
// Test Device/List with Status parameter
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== Testing Device/List with Status parameter ===\n\n";

// Test Status=1 (Active?)
foreach ([null, 0, 1, 2] as $status) {
    echo "--- Testing Status=$status ---\n";

    $params = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterCustomerCodes' => ['W9OPXL0YDK'],
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'Id',
        'SortOrder' => 0
    ];

    if ($status !== null) {
        $params['Status'] = $status;
    }

    $payload = json_encode([
        'action' => 'Device/List',
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
        echo "  Failed to fetch\n\n";
        continue;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
        echo "  Error: " . ($data['error'] ?? 'Unknown') . "\n\n";
        continue;
    }

    $raw = $data['data'] ?? [];
    $devices = $raw['Items'] ?? $raw['Result'] ?? $raw;

    echo "  Devices: " . count($devices) . "\n";

    if (!empty($devices)) {
        // Search for FQ966
        foreach ($devices as $device) {
            $json_str = json_encode($device);
            if (stripos($json_str, 'FQ966') !== false) {
                echo "  *** FOUND FQ966! ***\n";
                echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                break;
            }
        }

        // Show first asset
        $first = $devices[0];
        $asset = $first['AssetNumber'] ?? 'N/A';
        echo "  First asset: $asset\n";
    }

    echo "\n";
}

// Now test with ALL customers and different Status values
echo "\n=== Testing ALL CUSTOMERS with Status ===\n\n";

foreach ([null, 0, 1] as $status) {
    echo "--- All Customers, Status=$status ---\n";

    $params = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'PageNumber' => 1,
        'PageRows' => 200,
        'SortColumn' => 'AssetNumber',
        'SortOrder' => 'Asc'
    ];

    if ($status !== null) {
        $params['Status'] = $status;
    }

    $payload = json_encode([
        'action' => 'Device/List',
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
        echo "  Failed\n\n";
        continue;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
        echo "  Error\n\n";
        continue;
    }

    $raw = $data['data'] ?? [];
    $devices = $raw['Items'] ?? $raw['Result'] ?? $raw;

    echo "  Devices: " . count($devices) . "\n";

    if (!empty($devices)) {
        // Search for FQ966
        foreach ($devices as $device) {
            $json_str = json_encode($device);
            if (stripos($json_str, 'FQ966') !== false) {
                echo "  *** FOUND FQ966! ***\n";
                echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                exit(0);
            }
        }
    }

    echo "\n";
}

echo "FQ966 not found in any Status configuration\n";
