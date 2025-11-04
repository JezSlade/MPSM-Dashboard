<?php
// Test Device/List pagination
header('Content-Type: text/plain');

for ($page = 1; $page <= 10; $page++) {
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => 'SZ13qRwU5GtFLj0i_CbEgQ2',
            'FilterDealerCodes' => ['SYSTEL'],
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
        echo "Page $page: Failed to fetch\n";
        break;
    }

    $data = json_decode($response, true);

    if (!($data['success'] ?? false)) {
        echo "Page $page: Error\n";
        break;
    }

    $raw = $data['data'] ?? [];

    $count = 0;
    if (isset($raw['Items'])) {
        $count = count($raw['Items']);
    } elseif (is_array($raw)) {
        $count = count($raw);
    }

    echo "Page $page: $count devices\n";

    if ($count === 0 || $count < 200) {
        echo "Stopping (got < 200)\n";
        break;
    }
}
