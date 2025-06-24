<?php
// File: includes/env_parser.php
// -------------------------------------------------------------------
// Robust .env parser that defines each KEY as a PHP constant.
// Skips comments and empty lines, trims quotes.
// Throws if a required key is missing.
// -------------------------------------------------------------------

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env file not found at ' . $envFile);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    // skip comments
    if (strlen($line) === 0 || $line[0] === '#') {
        continue;
    }
    // parse KEY=VALUE
    if (strpos($line, '=') === false) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $key   = trim($key);
    $value = trim($value);

    // remove surrounding quotes if present
    if (($value[0] === '"' && substr($value, -1) === '"')
     || ($value[0] === "'" && substr($value, -1) === "'")) {
        $value = substr($value, 1, -1);
    }

    if (!defined($key)) {
        define($key, $value);
    }
}

// List all required .env keys here:
$required = [
    'CLIENT_ID',
    'CLIENT_SECRET',
    'USERNAME',
    'PASSWORD',
    'SCOPE',
    'TOKEN_URL',
    'API_BASE_URL',
    'DEALER_CODE',
    // if you added plugin token earlier:
    // 'PLUGIN_BEARER_TOKEN',
];

foreach ($required as $const) {
    if (!defined($const) || constant($const) === '') {
        throw new RuntimeException("Missing required .env key: {$const}");
    }
}
