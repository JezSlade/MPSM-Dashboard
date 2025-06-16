<?php
// --- DEBUG ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// --- CONFIG ---
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_FILE', CACHE_DIR . 'data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');

// --- Ensure cache dir exists ---
if (!is_dir(CACHE_DIR)) {
  mkdir(CACHE_DIR, 0755, true);
}

// --- Scoped include wrapper ---
// Prevents load_env() or globals from conflicting across API files
if (!function_exists('exec_api_file')) {
  function exec_api_file(string $file, string $customer): mixed {
    return (function () use ($file, $customer) {
      $_GET['customer'] = $customer;
      ob_start();
      include __DIR__ . '/../api/' . $file;
      return json_decode(ob_get_clean(), true);
    })();
  }
}

// --- Build merged dataset ---
$new = [
  'timestamp'    => date('c'),
  'devices'      => exec_api_file('get_devices.php', DEFAULT_CUSTOMER),
  'alerts'       => exec_api_file('get_device_alerts.php', DEFAULT_CUSTOMER),
  'counters'     => exec_api_file('get_device_counters.php', DEFAULT_CUSTOMER),
  'customers'    => exec_api_file('get_customers.php', DEFAULT_CUSTOMER),
  'contracts'    => exec_api_file('get_contracts.php', DEFAULT_CUSTOMER),
  'device_info'  => exec_api_file('get_device_info.php', DEFAULT_CUSTOMER)
];

// --- Write to cache ---
file_put_contents(CACHE_FILE, json_encode($new, JSON_PRETTY_PRINT));
