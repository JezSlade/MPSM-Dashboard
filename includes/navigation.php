<?php declare(strict_types=1);
// /includes/navigation.php

// 1) Shared helpers + config loader
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Build a GetCustomersRequest payload
$payload = [
    'DealerCode' => $config['DEALER_CODE'] ?? '',
    'PageNumber' => 1,
    'PageRows'   => 2147483647,
    'SortColumn' => 'Description',  // sort by the customer description
    'SortOrder'  => 'Asc',
];

try {
    // 3) Call the API
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', $payload);

    // 4) Handle API‐level validation errors
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'Unknown API error');
    }

    $customers = $resp['Result'] ?? [];
    $error     = '';
} catch (\Throwable $e) {
    $customers = [];
    $error     = $e->getMessage();
}

// 5) Render navigation list or error
if ($error !== '') {
    echo "<div class='nav-error'>Error loading customers: "
       . htmlspecialchars($error)
       . "</div>";
} else {
    echo "<ul class='nav-list'>";
    foreach ($customers as $cust) {
        // CustomerListDto uses 'Code' and 'Description'
        $code = htmlspecialchars($cust['Code'] ?? '');
        $name = htmlspecialchars($cust['Description'] ?? $code);
        echo "<li data-customer='{$code}'>{$name}</li>";
    }
    echo "</ul>";
}
