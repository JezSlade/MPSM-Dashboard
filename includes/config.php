<?php
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

$env = load_env();
define('APP_NAME', $env['APP_NAME'] ?? 'Glass App');
define('APP_VERSION', $env['APP_VERSION'] ?? '0.1.0');
define('BASE_URL', $env['BASE_URL'] ?? '/');

function render_view($path) {
    if (file_exists($path)) include $path;
}
?>
