<?php
// Check Customer/GetCustomers API
header('Content-Type: text/plain');

$payload = json_encode([
    'action' => 'Customer/GetCustomers',
    'params' => [
        'DealerCode' => 'SYSTEL',
        'PageNumber' => 1,
        'PageRows' => 500,
        'SortColumn' => 'Description',
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
    die("Failed to fetch customers\n");
}

$data = json_decode($response, true);

echo "Success: " . (($data['success'] ?? false) ? 'true' : 'false') . "\n";

if (!($data['success'] ?? false)) {
    echo "Error message: " . ($data['message'] ?? 'No message') . "\n";
    echo "Full response:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    die();
}

if ($data['success'] ?? false) {
    $customers = $data['data'] ?? [];

    echo "Customers returned: " . count($customers) . "\n\n";

    if (count($customers) > 0) {
        echo "First 10 customers:\n";
        foreach (array_slice($customers, 0, 10) as $cust) {
            $id = $cust['Id'] ?? $cust['CustomerId'] ?? 'N/A';
            $name = $cust['Description'] ?? $cust['CustomerName'] ?? 'N/A';
            echo "  $id - $name\n";
        }
    }

    // Check if FQ966 might be in a customer name
    foreach ($customers as $cust) {
        $json_str = json_encode($cust);
        if (stripos($json_str, 'FQ966') !== false) {
            echo "\n*** Customer with FQ966 found! ***\n";
            echo json_encode($cust, JSON_PRETTY_PRINT) . "\n";
        }
    }
}
