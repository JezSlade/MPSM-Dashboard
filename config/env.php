<?php
/**
 * Simple .env loader without Composer:
 * Reads KEY=VALUE lines from .env into $_ENV.
 */
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (!strpos($line, '=')) {
            continue;
        }
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        if (substr($val, 0, 1) === '"' && substr($val, -1) === '"') {
            $val = substr($val, 1, -1);
        }
        $_ENV[$key] = $val;
    }
}
loadEnv(__DIR__ . '/../.env');

function env($key, $default = null) {
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}
