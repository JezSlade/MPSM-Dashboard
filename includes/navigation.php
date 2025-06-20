<?php declare(strict_types=1);
// /includes/navigation.php

// Skip inside API responses
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

require_once __DIR__ . '/api_functions.php';
require_once __DIR__ . '/debug.php';

$config = parse_env_file(__DIR__ . '/../.env');

try {
    $resp      = call_api($config, 'POST', 'Customer/GetCustomers', []);
    $customers = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    error_log('Navigation API error: ' . $e->getMessage());
    $customers = [];
}

echo '<nav class="main-nav"><ul>';
if (!$customers) {
    echo '<li><em>No customers found</em></li>';
} else {
    foreach ($customers as $cust) {
        $code = htmlspecialchars($cust['CustomerCode'], ENT_QUOTES);
        $name = htmlspecialchars($cust['Name'],         ENT_QUOTES);
        echo "<li><a href=\"?customer={$code}\">{$name}</a></li>";
    }
}
echo '</ul></nav>';

appendDebug('Navigation rendered (' . count($customers) . ' customers)');
