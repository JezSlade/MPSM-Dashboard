<?php
// --- DEBUG + HARDENING ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// -------------------------

define('CACHE_FILE', __DIR__ . '/../cache/data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');

$customer = DEFAULT_CUSTOMER;

// Internal execution of API PHP endpoints
function exec_api($endpoint, $customer) {
  $_GET['customer'] = $customer;

  // Isolate output
  ob_start();
  include __DIR__ . '/../api/' . $endpoint;
  $json = ob_get_clean();

  return json_decode($json, true);
}

// Load previous cache (if exists)
$previous = file_exists(CACHE_FILE)
  ? json_decode(file_get_contents(CACHE_FILE), true)
  : [];

// Build new full-cache set
$new = [
  'timestamp'  => date('c'),
  'devices'    => exec_api('get_devices.php', $customer),
  'alerts'     => exec_api('get_device_alerts.php', $customer),
  'counters'   => exec_api('get_device_counters.php', $customer),
  'customers'  => exec_api('get_customers.php', $customer)
];

// Only overwrite if changed
if (json_encode($new) !== json_encode($previous)) {
  file_put_contents(CACHE_FILE, json_encode($new, JSON_PRETTY_PRINT));
  echo "[CACHE] Updated @ " . date('Y-m-d H:i:s') . "\n";
} else {
  echo "[CACHE] No changes. Skipped write.\n";
}
