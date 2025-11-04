<?php
// Test Device/Deleted/ListByDealer API
header('Content-Type: text/plain');

$payload = json_encode([
    'action' => 'Device/Deleted/ListByDealer',
    'params' => [
        'DealerCode' => 'SYSTEL',
        'PageNumber' => 1,
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

echo "Response received: " . (($response !== false) ? 'Yes' : 'No') . "\n";

if ($response !== false) {
    $data = json_decode($response, true);
    echo "Success: " . (($data['success'] ?? false) ? 'true' : 'false') . "\n";
    echo "Data type: " . gettype($data['data'] ?? null) . "\n";

    if (isset($data['data']) && is_array($data['data'])) {
        echo "Device count: " . count($data['data']) . "\n\n";

        // Search for FQ966
        foreach ($data['data'] as $device) {
            $json_str = json_encode($device);
            if (stripos($json_str, 'FQ966') !== false) {
                echo "FOUND FQ966 IN DELETED DEVICES!\n";
                echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                break;
            }
        }

        if (count($data['data']) > 0) {
            echo "Sample device keys: " . implode(', ', array_keys($data['data'][0])) . "\n";
        }
    } else {
        echo "Full response:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}
