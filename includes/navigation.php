<?php declare(strict_types=1);
// /includes/navigation.php

// 1. Pull in shared API helpers
require_once __DIR__ . '/api_functions.php';

// 2. Load configuration
$config = parse_env_file(__DIR__ . '/../.env');

// 3. Fetch customer list via internal call_api (no HTTP warnings)
try {
    $resp      = call_api($config, 'POST', 'Customer/GetCustomers', []);
    $customers = $resp['Result'] ?? [];
    $error     = '';
} catch (\Throwable $e) {
    $customers = [];
    $error     = $e->getMessage();
}

// 4. Render navigation
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
