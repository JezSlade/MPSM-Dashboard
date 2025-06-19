<?php declare(strict_types=1);
// /includes/navigation.php

// Prevent loading on API endpoints
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

require_once __DIR__ . '/api_functions.php';
require_once __DIR__ . '/searchable_dropdown.php';

$config = parse_env_file(__DIR__ . '/../.env');

// Determine payload for customers list
$payload = [
    'DealerCode' => $config['DEALER_CODE'] ?? '',
    'PageNumber' => 1,
    'PageRows'   => 2147483647,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
];
try {
    $resp      = call_api($config, 'POST', 'Customer/GetCustomers', $payload);
    $customers = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    $customers = [];
}

// Find current selection
$currentCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? '';

// Render the unified searchable dropdown
renderSearchableDropdown(
    'nav-customer-combobox',    // input ID
    'nav-customer-list',        // datalist ID
    '/api/get_customers.php',   // API endpoint
    'customer',                 // cookie name
    '— choose a customer —'     // placeholder
);
