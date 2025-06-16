<?php
// --- DEBUG SETUP ---
ini_set('memory_limit', '1G');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_FILE', CACHE_DIR . 'data.json');
define('DEFAULT_CUSTOMER', 'W9OPXL0YDK');
define('LOG_FILE', __DIR__ . '/../logs/cache_debug.log');

// 🪵 Ensure /logs directory exists
if (!is_dir(dirname(LOG_FILE))) {
  mkdir(dirname(LOG_FILE), 0755, true);
}

// 🪵 Log startup
file_put_contents(LOG_FILE, "[ENGINE] Started at " . date('c') . "\n", FILE_APPEND);

// 🔒 Ensure /cache directory exists
if (!is_dir(CACHE_DIR)) {
  mkdir(CACHE_DIR, 0755, true);
  file_put_contents(LOG_FILE, "[ENGINE] Created /cache directory\n", FILE_APPEND);
}

// 🧠 Inject load_env() for get_token.php
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

// ✅ STEP 1: Get token
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
  file_put_contents(LOG_FILE, "[ENGINE] ❌ Failed to get token\n", FILE_APPEND);
  exit("[CACHE ENGINE ERROR] Could not get token.\n");
}

file_put_contents(LOG_FILE, "[ENGINE] ✅ Token acquired\n", FILE_APPEND);

// ✅ STEP 2: API file execution with token
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

// ✅ STEP 3: Build new cache data
$new = [
  'timestamp'    => date('c'),
  'devices'      => exec_api_file('get_devices.php',        DEFAULT_CUSTOMER, $token),
  'alerts'       => exec_api_file('get_device_alerts.php',  DEFAULT_CUSTOMER, $token),
  'counters'     => exec_api_file('get_device_counters.php',DEFAULT_CUSTOMER, $token),
  'customers'    => exec_api_file('get_customers.php',      DEFAULT_CUSTOMER, $token),
  'contracts'    => exec_api_file('get_contracts.php',      DEFAULT_CUSTOMER, $token),
  'device_info'  => exec_api_file('get_device_info.php',    DEFAULT_CUSTOMER, $token)
];

// ✅ STEP 4: Compare to existing
$previous = file_exists(CACHE_FILE)
  ? json_decode(file_get_contents(CACHE_FILE), true)
  : [];

if (json_encode($new) === json_encode($previous)) {
  file_put_contents(LOG_FILE, "[ENGINE] ⚠️ No changes detected, skipping write\n", FILE_APPEND);
  echo "[CACHE] No changes. Skipped write.\n";
  return;
}

// ✅ STEP 5: Encode JSON with diagnostics
$jsonOutput = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

if (json_last_error() !== JSON_ERROR_NONE) {
  $error = json_last_error_msg();
  file_put_contents(LOG_FILE, "[ENGINE] ❌ JSON encode failed: $error\n", FILE_APPEND);
  exit("[CACHE ENGINE ERROR] JSON encode failed: $error\n");
}

// ✅ STEP 6: Atomic write
$tempFile = CACHE_DIR . 'data.tmp';
$finalFile = CACHE_FILE;

if (file_put_contents($tempFile, $jsonOutput) === false) {
  file_put_contents(LOG_FILE, "[ENGINE] ❌ Failed to write temp file\n", FILE_APPEND);
  exit("[CACHE ENGINE ERROR] Failed to write cache file.\n");
}

if (!rename($tempFile, $finalFile)) {
  file_put_contents(LOG_FILE, "[ENGINE] ❌ Failed to rename temp to final file\n", FILE_APPEND);
  exit("[CACHE ENGINE ERROR] Failed to finalize cache file.\n");
}

file_put_contents(LOG_FILE, "[ENGINE] ✅ Cache written to $finalFile\n", FILE_APPEND);
echo "[CACHE] Updated @ " . date('Y-m-d H:i:s') . "\n";
