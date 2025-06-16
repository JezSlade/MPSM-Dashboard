<?php
// --- DEBUG SETUP ---
ini_set('memory_limit', '1G'); // 🔧 Increase memory limit
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_FILE', CACHE_DIR . 'data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');

// 🔒 Ensure /cache directory exists
if (!is_dir(CACHE_DIR)) {
  mkdir(CACHE_DIR, 0755, true);
}

// 🧠 Inject local copy of load_env() so get_token.php works
if (!function_exists('load_env')) {
  function load_env($path = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($path)) return $env;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
      if (str_starts_with(trim($line), '#')) continue;
      [$key, $val] = explode('=', $line, 2);
      $env[trim($key)] = trim($val);
    }
    return $env;
  }
}

// ✅ STEP 1: Get token using existing get_token.php logic
function fetch_token(): ?string {
  return (function () {
    ob_start();
    include __DIR__ . '/../api/get_token.php';
    $output = ob_get_clean();
    $parsed = json_decode($output, true);
    return $parsed['access_token'] ?? null;
  })();
}

$token = fetch_token();

if (!$token) {
  error_log('[CACHE ENGINE] Failed to retrieve access token.');
  exit("[CACHE ENGINE ERROR] Could not get token.\n");
}

// ✅ STEP 2: Scoped include for API files with token injection
if (!function_exists('exec_api_file')) {
  function exec_api_file(string $file, string $customer, string $token): mixed {
    return (function () use ($file, $customer, $token) {
      $_GET['customer'] = $customer;
      $_GET['token']    = $token;
      ob_start();
      include __DIR__ . '/../api/' . $file;
      return json_decode(ob_get_clean(), true);
    })();
  }
}

// ✅ STEP 3: Build cache dataset
$new = [
  'timestamp'    => date('c'),
  'devices'      => exec_api_file('get_devices.php',        DEFAULT_CUSTOMER, $token),
  'alerts'       => exec_api_file('get_device_alerts.php',  DEFAULT_CUSTOMER, $token),
  'counters'     => exec_api_file('get_device_counters.php',DEFAULT_CUSTOMER, $token),
  'customers'    => exec_api_file('get_customers.php',      DEFAULT_CUSTOMER, $token),
  'contracts'    => exec_api_file('get_contracts.php',      DEFAULT_CUSTOMER, $token),
  'device_info'  => exec_api_file('get_device_info.php',    DEFAULT_CUSTOMER, $token)
];

// ✅ STEP 4: Compare previous cache
$previous = file_exists(CACHE_FILE)
  ? json_decode(file_get_contents(CACHE_FILE), true)
  : [];

if (json_encode($new) === json_encode($previous)) {
  echo "[CACHE] No changes. Skipped write.\n";
  return;
}

// ✅ STEP 5: Encode + write atomically with full protection
ob_clean();
$jsonOutput = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

if (json_last_error() !== JSON_ERROR_NONE) {
  $error = json_last_error_msg();
  error_log("[CACHE ENGINE] JSON encode failed: $error");
  exit("[CACHE ENGINE ERROR] JSON encode failed: $error\n");
}

$tempFile = CACHE_DIR . 'data.tmp';
$finalFile = CACHE_FILE;

if (file_put_contents($tempFile, $jsonOutput) === false) {
  error_log("[CACHE ENGINE] Failed to write temporary cache file.");
  exit("[CACHE ENGINE ERROR] Failed to write cache file.\n");
}

if (!rename($tempFile, $finalFile)) {
  error_log("[CACHE ENGINE] Failed to move cache file into place.");
  exit("[CACHE ENGINE ERROR] Failed to finalize cache file.\n");
}

echo "[CACHE] Updated @ " . date('Y-m-d H:i:s') . "\n";
