<?php
// core/config.php
// v1.0.2 [Load .env → constants; display errors but suppress notices/warnings in DEBUG]

function loadEnv(string $path): array {
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $vars  = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (!$line || $line[0] === '#') continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $vars[$key] = $value;
    }
    return $vars;
}

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die('Error: .env file not found');
}
$env = loadEnv($envPath);

foreach ($env as $key => $value) {
    if (!defined($key)) define($key, $value);
}
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
if (!defined('DEBUG'))       define('DEBUG', 'false');

// In DEBUG, show all except notices/warnings; in production, hide all
if (DEBUG === 'true') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
