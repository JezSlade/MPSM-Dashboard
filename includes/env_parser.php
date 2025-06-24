<?php
// includes/env_parser.php
// -------------------------------------------------------------------
// Robust .env loader that logs each key as it’s defined and
// throws if any required constant (including DEALER_CODE) is missing.
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
    list($key, $value) = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    // Strip optional surrounding quotes
    if (
        (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
    ) {
        $value = substr($value, 1, -1);
    }

    if (!defined($key)) {
        define($key, $value);
        $defined[] = $key;
    }
}

// Required keys
$required = [
    'CLIENT_ID',
    'CLIENT_SECRET',
    'USERNAME',
    'PASSWORD',
    'SCOPE',
    'TOKEN_URL',
    'API_BASE_URL',
    'DEALER_CODE',            // ensure this is present
    // 'PLUGIN_BEARER_TOKEN',  // if used
];

$missing = array_diff($required, $defined);
if (!empty($missing)) {
    throw new RuntimeException('Missing .env keys: ' . implode(', ', $missing));
}

// Debug: log which keys were loaded (can remove in production)
error_log('Loaded .env keys: ' . implode(', ', $defined));
