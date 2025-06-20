<?php declare(strict_types=1);
// includes/api_functions.php

function parse_env_file(string $path): array {
    $lines = @file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $env = [];
    if ($lines) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!strpos($line, '=')) continue;
            list($k,$v) = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
    }
    return $env;
}

function call_api(array $config, string $method, string $endpoint, array $payload): array {
    // TODO: implement actual OAuth + cURL
    return [];
}
