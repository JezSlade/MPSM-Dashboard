<?php declare(strict_types=1);
// /includes/navigation.php — main site nav

// Skip if this is an API request
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = [];  // adjust if you need filters

try {
    $resp      = call_api($config, 'POST', 'Customer/GetCustomers', $payload);
    $customers = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    error_log("Navigation API error: " . $e->getMessage());
    $customers = [];
}

// render nav
echo '<nav class="main-nav"><ul>';
foreach ($customers as $cust) {
    $code = htmlspecialchars($cust['CustomerCode'], ENT_QUOTES);
    $name = htmlspecialchars($cust['Name'],         ENT_QUOTES);
    echo "<li><a href=\"?customer={$code}\">{$name}</a></li>";
}
echo '</ul></nav>';

// send actual count to the live-debug console
$navCount = count($customers);
echo "<script>appendDebug('▶ Navigation rendered ({$navCount} customers)');</script>";
