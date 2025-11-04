<?php
// Search for FQ966 in ALL fields of Counter/ListDetailed for one customer
header('Content-Type: text/plain');
require '../config.php';
require '../functions.php';
requireAuth();

echo "=== Searching for FQ966 in ALL FIELDS ===\n\n";

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

if (!($data['success'] ?? false)) {
    die("Error: " . json_encode($data, JSON_PRETTY_PRINT) . "\n");
}

$devices = $data['data'] ?? [];

echo "Total devices to search: " . count($devices) . "\n\n";

// Search for FQ966 in ANY field
foreach ($devices as $device) {
    $json_str = json_encode($device);
    if (stripos($json_str, 'FQ966') !== false) {
        echo "*** FOUND FQ966! ***\n\n";
        echo json_encode($device, JSON_PRETTY_PRINT) . "\n\n";

        // Show which field contains FQ966
        foreach ($device as $key => $value) {
            if (is_string($value) && stripos($value, 'FQ966') !== false) {
                echo "Found in field: $key = $value\n";
            } elseif (is_array($value)) {
                $val_str = json_encode($value);
                if (stripos($val_str, 'FQ966') !== false) {
                    echo "Found in field: $key (nested object/array)\n";
                    echo json_encode($value, JSON_PRETTY_PRINT) . "\n";
                }
            }
        }

        exit(0);
    }
}

echo "FQ966 NOT FOUND in any field of " . count($devices) . " devices\n\n";

// Show sample device structure
if (!empty($devices)) {
    echo "Sample device structure (first device):\n";
    echo json_encode($devices[0], JSON_PRETTY_PRINT) . "\n";
}
