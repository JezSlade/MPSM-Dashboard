<?php
// Test Device/Deleted/ListByDealer API
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

$response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

echo "Response:\n";
echo $response;
echo "\n\n";

$data = json_decode($response, true);
echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "Data type: " . gettype($data['data']) . "\n";

if (is_array($data['data'])) {
    echo "Device count: " . count($data['data']) . "\n";
    if (count($data['data']) > 0) {
        echo "First device: " . json_encode($data['data'][0], JSON_PRETTY_PRINT) . "\n";
    }
}
