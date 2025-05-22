<?php
// core/config.php
// v1.0.0 [Load .env into constants]

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
