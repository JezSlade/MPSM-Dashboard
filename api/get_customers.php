<?php
// --- DEBUG BLOCK ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

header('Content-Type: application/json');

// Load .env function (guarded)
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

// 🛑 Dual-mode execution
if (isset($_GET['token'])) {
  $env = load_env();
  $token = $_GET['token'];

  $api_url = rtrim($env['API_BASE_URL'], '/') . '/Customer/GetCustomers';

  $payload = [
    'DealerCode'  => $env['DEALER_CODE'],
    'Code'        => null,
    'HasHpSds'    => null,
    'FilterText'  => null,
    'PageNumber'  => 1,
    'PageRows'    => 2147483647,
    'SortColumn'  => 'Id',
    'SortOrder'   => 0
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "Accept: application/json"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

  $response = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  http_response_code($code);
  echo $response;
  return;
}

// 🔁 Otherwise, serve from cache
$cache = file_get_contents(__DIR__ . '/../cache/data.json');
$json = json_decode($cache, true);
echo json_encode($json['customers'] ?? []);
exit;
