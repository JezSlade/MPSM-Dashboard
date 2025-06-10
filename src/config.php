<?php
// src/config.php
// ---------------------------
// Load .env into getenv()/$_ENV and define core constants.
// Enable full PHP error reporting.
// ---------------------------

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path to .env (one level up)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        list($key, $val) = explode('=', $line, 2);
        $val = trim($val, "\"'");
        putenv("$key=$val");
        $_ENV[$key] = $val;
    }
} else {
    trigger_error(".env file not found at $envPath", E_USER_WARNING);
}

// Define constants, using empty string if missing
define('CLIENT_ID',       getenv('CLIENT_ID')       ?: '');
define('CLIENT_SECRET',   getenv('CLIENT_SECRET')   ?: '');
define('USERNAME',        getenv('USERNAME')        ?: '');
define('PASSWORD',        getenv('PASSWORD')        ?: '');
define('SCOPE',           getenv('SCOPE')           ?: '');
define('TOKEN_URL',       getenv('TOKEN_URL')       ?: '');
define('API_BASE_URL',    getenv('BASE_URL')        ?: '');
define('DEALER_CODE',     getenv('DEALER_CODE')     ?: '');
define('DEALER_ID',       getenv('DEALER_ID')       ?: '');
define('DEVICE_PAGE_SIZE',getenv('DEVICE_PAGE_SIZE')?: '');

// Toggle debug logging on/off
define('DEBUG_MODE', true);
