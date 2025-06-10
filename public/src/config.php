<?php
// public/src/config.php
// -----------------------------------------------------
// Load .env from up to three parent directories:
// 1) public/src/../.env
// 2) public/src/../../.env
// 3) public/src/../../../.env (project root)
// -----------------------------------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$envPaths = [
    __DIR__ . '/../.env',
    dirname(__DIR__, 2) . '/.env',
    dirname(__DIR__, 3) . '/.env'
];

foreach ($envPaths as $path) {
    if (file_exists($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $val = trim($val, "\"'");
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
        break;
    }
}

// Define constants, falling back to empty strings
define('CLIENT_ID',        getenv('CLIENT_ID')        ?: '');
define('CLIENT_SECRET',    getenv('CLIENT_SECRET')    ?: '');
define('USERNAME',         getenv('USERNAME')         ?: '');
define('PASSWORD',         getenv('PASSWORD')         ?: '');
define('SCOPE',            getenv('SCOPE')            ?: '');
define('TOKEN_URL',        getenv('TOKEN_URL')        ?: '');
define('API_BASE_URL',     getenv('BASE_URL')         ?: '');
define('DEALER_CODE',      getenv('DEALER_CODE')      ?: '');
define('DEALER_ID',        getenv('DEALER_ID')        ?: '');
define('DEVICE_PAGE_SIZE', getenv('DEVICE_PAGE_SIZE') ?: '');

// Debug toggle
define('DEBUG_MODE', true);
