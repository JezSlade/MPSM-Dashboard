<?php declare(strict_types=1);
// includes/env_parser.php
// -------------------------------------------------------------------
// Loads .env into constants, overriding any empty stubs.
// Throws if any required key is still missing or empty.
// -------------------------------------------------------------------

require_once __DIR__ . '/constants.php';

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env file not found at ' . $envFile);
}

$definedNow = [];
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
    $key = trim($key);
    $val = trim($rawVal);

    // Strip surrounding quotes
    if (
        (substr($val, 0, 1) === '"' && substr($val, -1) === '"') ||
        (substr($val, 0, 1) === "'" && substr($val, -1) === "'")
    ) {
        $val = substr($val, 1, -1);
    }

    // Override stub if it’s empty, or define if missing
    if (!defined($key) || constant($key) === '') {
        define($key, $val);
    }
    $definedNow[] = $key;
}

// Enforce required keys
$required = [
    'CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD',
    'SCOPE','TOKEN_URL','API_BASE_URL','DEALER_CODE',
    // 'PLUGIN_BEARER_TOKEN' if you’re using plugin auth
];

$missing = array_filter($required, function($k){
    return !defined($k) || constant($k) === '';
});

if (!empty($missing)) {
    throw new RuntimeException('Missing .env keys: ' . implode(', ', $missing));
}

// Optional debug
// error_log('Loaded .env keys: ' . implode(', ', $definedNow));
