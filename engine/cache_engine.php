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

// 🛡️ Ensure /cache exists
if (!is_dir(CACHE_DIR)) {
  mkdir(CACHE_DIR, 0755, true);
  echo "[ENGINE] Created /cache directory\n";
}

// 🧠 Inject local load_env
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
  echo "[ENGINE] Fetching token...\n";
  ob_start();
  include __DIR__ . '/../api/get_token.php';
  $output = ob_get_clean();
  $parsed = json_decode($output, true);
  if (!isset($parsed['access_token'])) {
    echo "[ENGINE] ❌ Token fetch failed:\n";
    var_dump($parsed);
  }
  return $parsed['access_token'] ?? null;
}

$token = fetch_token();
if (!$token) {
  echo "[ENGINE ERROR] Could not get token\n";
  exit;
}
echo "[ENGINE] ✅ Token OK\n";

// ✅ STEP 2: Inline API pull
function exec_api_file(string $file, string $customer, string $token): mixed {
  echo "[ENGINE] → Including $file...\n";
  return (function () use ($file, $customer, $token) {
    $_GET['customer'] = $customer;
    $_GET['token']    = $token;
    ob_start();
    include __DIR__ . '/../api/' . $file;
    $raw = ob_get_clean();
    $parsed = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      echo "[ENGINE] ❌ JSON Error in $file: " . json_last_error_msg() . "\n";
      echo "Raw output:\n$raw\n";
      exit;
    }
    return $parsed;
  })();
}

// ✅ STEP 3: Build minimal dataset
echo "[ENGINE] → Starting dataset build...\n";
$new = [
  'timestamp' => date('c'),
  'devices'   => exec_api_file('get_devices.php', DEFAULT_CUSTOMER, $token)
];

echo "[ENGINE] ✅ Data collected\n";

// ✅ STEP 4: Save
echo "[ENGINE] → Saving cache to disk...\n";
$json = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo "[ENGINE] ❌ JSON encode failed: " . json_last_error_msg() . "\n";
  exit;
}

$tempFile  = CACHE_DIR . 'data.tmp';
$finalFile = CACHE_FILE;

if (file_put_contents($tempFile, $json) === false) {
  echo "[ENGINE] ❌ Could not write temp file\n";
  exit;
}

if (!rename($tempFile, $finalFile)) {
  echo "[ENGINE] ❌ Could not move temp into place\n";
  exit;
}

echo "[ENGINE] ✅ Cache saved to $finalFile\n";
