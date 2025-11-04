<?php
// Test Device/List with FilterDealerCodes
header('Content-Type: text/plain');

$payload = json_encode([
    'action' => 'Device/List',
    'params' => [
        'FilterDealerCodes' => ['SYSTEL'],
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
        if (isset($raw['Items'])) {
            echo "Items count: " . count($raw['Items']) . "\n";

            // Search for FQ966
            foreach ($raw['Items'] as $device) {
                $json_str = json_encode($device);
                if (stripos($json_str, 'FQ966') !== false) {
                    echo "\n*** FOUND FQ966! ***\n";
                    echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
                    break;
                }
            }

            if (!empty($raw['Items'])) {
                echo "\nFirst device sample:\n";
                echo json_encode($raw['Items'][0], JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "No Items wrapper, direct array\n";
            echo "Count: " . count($raw) . "\n";
        }
    }
}
