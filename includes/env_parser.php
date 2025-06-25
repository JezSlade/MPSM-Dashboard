<?php declare(strict_types=1);
// includes/env_parser.php
// -------------------------------------------------------------------
// Simple .env loader: defines each KEY only once, throws if missing.
// -------------------------------------------------------------------

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env file not found at ' . $envFile);
}

$defined = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    if (strpos($line, '=') === false) {
        continue;
    }
    list($key, $rawVal) = explode('=', $line, 2);
    $key    = trim($key);
    $value  = trim($rawVal, " \t\n\r\0\x0B"); // trim whitespace only

    // Strip optional surrounding quotes
    if (
        (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
    ) {
        $value = substr($value, 1, -1);
    }

    // Define if not already defined
    if (!defined($key)) {
        define($key, $value);
        $defined[] = $key;
    }
}

// Required keys from AllEndpoints.json and plugin auth
$required = [
    'CLIENT_ID',
    'CLIENT_SECRET',
    'USERNAME',
    'PASSWORD',
    'SCOPE',
    'TOKEN_URL',
    'API_BASE_URL',
    'DEALER_CODE',
    // 'PLUGIN_BEARER_TOKEN', // include if you use plugin_auth
];

$missing = array_filter($required, function($k) {
    return !defined($k) || constant($k) === '';
});

if (!empty($missing)) {
    throw new RuntimeException('Missing .env keys: ' . implode(', ', $missing));
}

// Optional: log loaded keys for debugging
// error_log('Loaded .env keys: ' . implode(', ', $defined));
