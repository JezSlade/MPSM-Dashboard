<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

if (!function_exists('load_env')) {
  function load_env($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
      if (str_starts_with(trim($line), '#')) continue;
      [$key, $val] = explode('=', $line, 2);
      $env[trim($key)] = trim($val);
    }
    return $env;
  }
}

$env = load_env();

define('API_BASE_URL', $env['API_BASE_URL'] ?? '');
define('APP_BASE_URL', $env['APP_BASE_URL'] ?? '/');
define('APP_NAME', $env['APP_NAME'] ?? 'App');
define('APP_VERSION', $env['APP_VERSION'] ?? '0.0.1');

function render_view($path) {
  if (file_exists($path)) include $path;
}
?>
