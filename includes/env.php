<?php
// Manual .env parser
function parse_env_file(string $path): array {
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    if ($lines) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $env[trim($key)] = trim($val);
            }
        }
    }
    return $env;
}

// Load into getenv/$_ENV/$_SERVER
$env = parse_env_file(__DIR__ . '/../.env');
foreach ($env as $k => $v) {
    if (getenv($k) === false) {
        putenv("{$k}={$v}");
        $_ENV[$k]     = $v;
        $_SERVER[$k]  = $v;
    }
}
