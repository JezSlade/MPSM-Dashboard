<?php
// --- DEBUG + LOGGING ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ------------------------

define('CACHE_FILE', __DIR__ . '/../cache/data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');

// 🔐 Scoped API execution wrapper
function exec_api(string $endpoint, string $customer): mixed {
  $_GET['customer'] = $customer;

  // Sandbox wrapper to prevent global function conflicts
  return (function () use ($endpoint) {
    ob_start();
    include __DIR__ . '/../api/' . $endpoint;
    return json_decode(ob_get_clean(), true);
  })();
}

// 📦 Load previous cache (if exists)
$previous = file_exists(CACHE_FILE)
  ? json_decode(file_get_contents(CACHE_FILE), true)
  : [];

// 📥 Build full cache object
$new = [
  'timestamp'  => date('c'),
  'devices'    => exec_api('get_devices.php', DEFAULT_CUSTOMER),
  'alerts'     => exec_api('get_device_alerts.php', DEFAULT_CUSTOMER),
  'counters'   => exec_api('get_device_counters.php', DEFAULT_CUSTOMER),
  'customers'  => exec_api('get_customers.php', DEFAULT_CUSTOMER)
];

// 🧠 Save only if content changed
if (json_encode($new) !== json_encode($previous)) {
  file_put_contents(CACHE_FILE, json_encode($new, JSON_PRETTY_PRINT));
  echo "[CACHE] Updated @ " . date('Y-m-d H:i:s') . "\n";
} else {
  echo "[CACHE] No changes. Skipped write.\n";
}
