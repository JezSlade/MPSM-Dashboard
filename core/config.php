<?php
// core/config.php
// v1.0.3 [Improved error logic + comments]

/**
 * Parses a .env file into an associative array of environment variables.
 */
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

// Load .env file
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die('Error: .env file not found');
}
$env = loadEnv($envPath);

// Define each env var as a constant
foreach ($env as $key => $value) {
    if (!defined($key)) define($key, $value);
}

// Ensure fallback constants exist
define('ENVIRONMENT', defined('ENVIRONMENT') ? ENVIRONMENT : 'production');
define('DEBUG', defined('DEBUG') ? DEBUG : 'false');

// Configure error display based on DEBUG flag
if (DEBUG === 'true') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
