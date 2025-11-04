<?php
// Test with EXACT SDK parameters
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== Testing with EXACT SDK parameters ===\n\n";

$totalDevices = 0;

for ($page = 1; $page <= 50; $page++) {
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => null,
            'ProductBrand' => null,
            'ProductModel' => null,
            'OfficeId' => null,
            'Status' => 1,  // Active devices only
            'FilterText' => null,
            'PageNumber' => $page,
            'PageRows' => 50,  // SDK uses 50
            'SortColumn' => 'Id',  // SDK uses Id
            'SortOrder' => 0  // SDK uses 0
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
        echo "Page $page: Failed\n";
        break;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
        echo "Page $page: Error\n";
        break;
    }

    $raw = $data['data'] ?? [];
    $devices = $raw['Items'] ?? $raw['Result'] ?? $raw;

    if (empty($devices)) {
        echo "Page $page: Empty\n";
        break;
    }

    echo "Page $page: " . count($devices) . " devices\n";
    $totalDevices += count($devices);

    // Check for FQ966
    foreach ($devices as $device) {
        if (stripos(json_encode($device), 'FQ966') !== false) {
            echo "  → Found FQ966!\n";
        }
    }

    if (count($devices) < 50) {
        echo "Last page (got " . count($devices) . " < 50)\n";
        break;
    }

    // Stop after finding a reasonable amount
    if ($totalDevices > 3000) {
        echo "Stopping at 3000+ devices\n";
        break;
    }
}

echo "\n=== RESULTS ===\n";
echo "Total devices: $totalDevices\n";
