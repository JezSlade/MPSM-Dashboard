<?php
// Test Counter/ListDetailed endpoint
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== Testing Counter/ListDetailed ===\n\n";

// Test with empty filters (should return all devices for customer)
$payload = json_encode([
    'action' => 'Counter/ListDetailed',
    'params' => [
        'DealerCode' => 'NY06AGDWUQ',
        'CustomerCode' => 'W9OPXL0YDK',
        'SerialNumber' => '',
        'AssetNumber' => null,
        'CounterDetaildTags' => null
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
    die("Failed to fetch\n");
}

$data = json_decode($response, true);

echo "Success: " . (($data['success'] ?? false) ? 'true' : 'false') . "\n";

if (!($data['success'] ?? false)) {
    echo "Error: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    $raw = $data['data'] ?? [];

    if (is_array($raw)) {
        echo "Data type: array\n";

        if (isset($raw['Items'])) {
            $devices = $raw['Items'];
            echo "Devices (via Items): " . count($devices) . "\n\n";
        } elseif (isset($raw['Result'])) {
            $devices = $raw['Result'];
            echo "Devices (via Result): " . count($devices) . "\n\n";
        } else {
            $devices = $raw;
            echo "Devices (direct array): " . count($devices) . "\n\n";
        }

        if (!empty($devices)) {
            echo "First device keys: " . implode(', ', array_keys($devices[0])) . "\n\n";

            // Search for FQ966
            foreach ($devices as $device) {
                $json_str = json_encode($device);
                if (stripos($json_str, 'FQ966') !== false) {
                    echo "*** FOUND FQ966! ***\n";
                    echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                    break;
                }
            }

            echo "\nFirst 3 devices:\n";
            foreach (array_slice($devices, 0, 3) as $device) {
                $asset = $device['AssetNumber'] ?? $device['Asset'] ?? 'N/A';
                $serial = $device['SerialNumber'] ?? $device['Serial'] ?? 'N/A';
                echo "  Asset: $asset, Serial: $serial\n";
            }
        }
    }
}

// Now test with AssetNumber=FQ966
echo "\n\n=== Testing with AssetNumber=FQ966 ===\n\n";

$payload2 = json_encode([
    'action' => 'Counter/ListDetailed',
    'params' => [
        'DealerCode' => 'NY06AGDWUQ',
        'CustomerCode' => 'W9OPXL0YDK',
        'SerialNumber' => '',
        'AssetNumber' => 'FQ966',
        'CounterDetaildTags' => null
    ]
]);

$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $payload2,
        'timeout' => 15,
        'ignore_errors' => true
    ]
]);

$response2 = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context2);

if ($response2 !== false) {
    $data2 = json_decode($response2, true);

    if ($data2['success'] ?? false) {
        $devices2 = $data2['data'] ?? [];
        echo "Success! Found " . count($devices2) . " device(s) matching FQ966\n";

        if (!empty($devices2)) {
            echo json_encode($devices2, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "No match or error\n";
    }
}
