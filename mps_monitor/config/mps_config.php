<?php
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END
// Load .env manually
$dotenvPath = __DIR__ . '/../../../.env';
if (file_exists($dotenvPath)) {
  $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $_ENV[trim($name)] = trim($value);
  }
}

// Define expected constants
if (!defined('API_BASE_URL')) {
  define('API_BASE_URL', $_ENV['API_BASE_URL'] ?? '');
}
if (!defined('USERNAME')) {
  define('USERNAME', $_ENV['USERNAME'] ?? '');
}
if (!defined('PASSWORD')) {
  define('PASSWORD', $_ENV['PASSWORD'] ?? '');
}
if (!defined('CLIENT_ID')) {
  define('CLIENT_ID', $_ENV['CLIENT_ID'] ?? '');
}
if (!defined('CLIENT_SECRET')) {
  define('CLIENT_SECRET', $_ENV['CLIENT_SECRET'] ?? '');
}

// Validate critical values
$missing = [];
if (empty(API_BASE_URL)) $missing[] = 'API_BASE_URL';
if (empty(CLIENT_ID)) $missing[] = 'CLIENT_ID';
if (empty(CLIENT_SECRET)) $missing[] = 'CLIENT_SECRET';

if (!empty($missing)) {
  if (!headers_sent()) {
    http_response_code(500);
  }
  echo json_encode([
    'error' => 'Missing required configuration values.',
    'missing' => $missing
  ]);
  exit;
}
