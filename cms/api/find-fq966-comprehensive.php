<?php
// Comprehensive search for FQ966 across ALL pages of ALL devices
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== COMPREHENSIVE FQ966 SEARCH ===\n\n";

$found = false;
$totalDevices = 0;

// Search installed devices
for ($page = 1; $page <= 20; $page++) {
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
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
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        break;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
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
            echo "\n*** FOUND FQ966 ON PAGE $page! ***\n\n";
            echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
            $found = true;
            break 2;
        }
    }

    if (count($devices) < 200) {
        echo "Last page reached\n";
        break;
    }
}

if (!$found) {
    echo "\nFQ966 NOT FOUND in $totalDevices installed devices\n";
    echo "\nSearching deleted devices...\n\n";

    // Search deleted devices
    for ($page = 1; $page <= 10; $page++) {
        $payload = json_encode([
            'action' => 'Device/Deleted/ListByDealer',
            'params' => [
                'DealerCode' => DEFAULT_DEALER_CODE,
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
                'timeout' => 15,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

        if ($response === false) {
            break;
        }

        $data = json_decode($response, true);

        if (!($data['success'] ?? false)) {
            break;
        }

        $devices = $data['data'] ?? [];

        if (empty($devices)) {
            break;
        }

        echo "Deleted page $page: " . count($devices) . " devices\n";

        // Search for FQ966
        foreach ($devices as $device) {
            $json_str = json_encode($device);
            if (stripos($json_str, 'FQ966') !== false) {
                echo "\n*** FOUND FQ966 IN DELETED DEVICES (PAGE $page)! ***\n\n";
                echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                $found = true;
                break 2;
            }
        }

        if (count($devices) < 200) {
            break;
        }
    }
}

echo "\n=== SEARCH COMPLETE ===\n";
echo "Total devices scanned: $totalDevices\n";
echo "FQ966 found: " . ($found ? 'YES' : 'NO') . "\n";
