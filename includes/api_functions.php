<?php declare(strict_types=1);
// /includes/api_functions.php

function parse_env_file(string $path): array {
    // Minimal .env loader
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    if ($lines) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!strpos($line, '=')) continue;
            list($key, $val) = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
    }
    return $env;
}

function call_api(array $config, string $method, string $endpoint, array $payload): array {
    // Placeholder: implement OAuth + cURL here
    return [];
}
