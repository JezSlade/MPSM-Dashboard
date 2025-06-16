<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_FILE', CACHE_DIR . 'data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');

// 🔒 Make sure /cache directory exists
if (!is_dir(CACHE_DIR)) {
  mkdir(CACHE_DIR, 0755, true);
}

// 🔁 API include wrapped in scoped sandbox
function exec_api_file(string $file, string $customer): mixed {
  return (function() use ($file, $customer) {
    $_GET['customer'] = $customer;
    ob_start();
    include __DIR__ . '/../api/' . $file;
    return json_decode(ob_get_clean(), true);
  })();
}

// 🧠 Build new dataset
$new = [
  'timestamp'  => date('c'),
  'devices'    => exec_api_file('get_devices.php', DEFAULT_CUSTOMER),
  'alerts'     => exec_api_file('get_device_alerts.php', DEFAULT_CUSTOMER),
  'counters'   => exec_api_file('get_device_counters.php', DEFAULT_CUSTOMER),
  'customers'  => exec_api_file('get_customers.php', DEFAULT_CUSTOMER)
];

// 📥 Compare previous content
$previous = file_exists(CACHE_FILE)
  ? json_decode(file_get_contents(CACHE_FILE), true)
  : [];

if (json_encode($new) !== json_encode($previous)) {
  file_put_contents(CACHE_FILE, json_encode($new, JSON_PRETTY_PRINT));
  echo "[CACHE] Updated @ " . date('Y-m-d H:i:s') . "\n";
} else {
  echo "[CACHE] No changes. Skipped write.\n";
}
