<?php
// includes/env_parser.php

// Simple .env parser: loads key=value into defined constants

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env file not found at ' . $envFile);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // skip comments
    if (strpos(trim($line), '#') === 0) {
        continue;
    }
    // parse KEY=VALUE
    if (strpos($line, '=') !== false) {
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        // only define if not already defined
        if (!defined($key)) {
            define($key, $val);
        }
    }
}

// Required constants check
$required = [
    'CLIENT_ID', 'CLIENT_SECRET',
    'USERNAME', 'PASSWORD',
    'SCOPE', 'TOKEN_URL',
    'API_BASE_URL', 'DEALER_CODE',
];
foreach ($required as $const) {
    if (!defined($const)) {
        throw new RuntimeException("Missing required .env key: {$const}");
    }
}
