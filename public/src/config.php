<?php
// public/src/config.php
// ---------------------------
// Load environment variables from .env located
// either in public/ or one level above.
// ---------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// Determine .env location
$envPath = null;
$candidates = [
    __DIR__ . '/../.env',    // public/.env
    __DIR__ . '/../..../.env', // adjust if your structure differs
    __DIR__ . '/../../.env'  // project-root/.env
];
foreach ($candidates as $path) {
    if (file_exists($path)) {
        $envPath = $path;
        break;
    }
}

if ($envPath) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$key, $val] = explode('=', $line, 2);
        $val = trim($val, "\"'");
        putenv("$key=$val");
        $_ENV[$key] = $val;
    }
} else {
    // No .env found; you must set constants manually
}

// Define constants (falling back to empty)
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

// Debug toggle
define('DEBUG_MODE', true);
