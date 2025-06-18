<?php declare(strict_types=1);
// /includes/navigation.php

// 1) Load shared API helpers and config parser
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Prepare payload for Customer/GetCustomers
$payload = [
    'Code' => $config['DEALER_CODE'] ?? ''
];

// 3) Call the internal API and handle both transport and business‐logic errors
try {
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', $payload);

    // If the API itself returned a validation error, surface it
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'API returned an error');
    }

    $customers = $resp['Result'] ?? [];
    $error     = '';
} catch (\Throwable $e) {
    $customers = [];
    $error     = $e->getMessage();
}

// 4) Render the navigation
if ($error !== '') {
    echo "<div class='nav-error'>Error loading customers: "
       . htmlspecialchars($error)
       . "</div>";
} else {
    echo "<ul class='nav-list'>";
    foreach ($customers as $cust) {
        $code = htmlspecialchars($cust['CustomerCode'] ?? '');
        $name = htmlspecialchars($cust['Name'] ?? $code);
        echo "<li data-customer='{$code}'>{$name}</li>";
    }
    echo "</ul>";
}
