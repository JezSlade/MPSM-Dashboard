<?php
// Test Customer/List API
header('Content-Type: text/plain');

$payload = json_encode([
    'action' => 'Customer/List',
    'params' => [
        'FilterDealerCodes' => ['SYSTEL'],
        'PageNumber' => 1,
        'PageRows' => 1000
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
    echo "Error: " . ($data['error'] ?? 'Unknown') . "\n";
    echo "Full response:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    $raw = $data['data'] ?? [];
    echo "Data type: " . gettype($raw) . "\n";

    if (is_array($raw)) {
        echo "Data keys: " . implode(', ', array_keys($raw)) . "\n";

        if (isset($raw['Items'])) {
            echo "Items count: " . count($raw['Items']) . "\n";
            if (!empty($raw['Items'])) {
                echo "First item: " . json_encode($raw['Items'][0], JSON_PRETTY_PRINT) . "\n";
            }
        } elseif (isset($raw['Result'])) {
            echo "Result count: " . count($raw['Result']) . "\n";
        } else {
            echo "Direct array count: " . count($raw) . "\n";
            if (!empty($raw)) {
                echo "First item: " . json_encode($raw[0], JSON_PRETTY_PRINT) . "\n";
            }
        }
    }
}
