<?php
function parse_env_file($path) {
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
    return $env;
}
// Load .env
$env = parse_env_file(__DIR__ . '/../.env');
foreach ($env as $k => $v) {
    if (!getenv($k)) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}
?>