<?php
// Search for FQ966 in ALL deleted devices (all pages)
header('Content-Type: text/plain');

$found = false;
$totalDevices = 0;
$pageNumber = 1;
$maxPages = 20;

while ($pageNumber <= $maxPages && !$found) {
    $payload = json_encode([
        'action' => 'Device/Deleted/ListByDealer',
        'params' => [
            'DealerCode' => 'SYSTEL',
            'PageNumber' => $pageNumber,
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
        echo "Failed to fetch page $pageNumber\n";
        break;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['success']) || !$data['success']) {
        echo "API error on page $pageNumber\n";
        break;
    }

    $devices = $data['data'] ?? [];

    if (empty($devices)) {
        echo "No more devices after page " . ($pageNumber - 1) . "\n";
        break;
    }

    echo "Page $pageNumber: " . count($devices) . " devices\n";
    $totalDevices += count($devices);

    // Search for FQ966
    foreach ($devices as $device) {
        $json_str = json_encode($device);
        if (stripos($json_str, 'FQ966') !== false) {
            echo "\n*** FOUND FQ966 ON PAGE $pageNumber! ***\n\n";
            echo json_encode($device, JSON_PRETTY_PRINT) . "\n";
            $found = true;
            break;
        }
    }

    if (count($devices) < 200) {
        echo "Last page reached (got " . count($devices) . " < 200)\n";
        break;
    }

    $pageNumber++;
}

echo "\nTotal deleted devices checked: $totalDevices\n";
echo "FQ966 found: " . ($found ? 'YES' : 'NO') . "\n";
