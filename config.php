<?php
// Load environment variables safely
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Core Configuration
define('APP_NAME', getenv('APP_NAME') ?: 'MPS Widget CMS');
define('APP_VERSION', getenv('APP_VERSION') ?: '1.0.0');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: (
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
    "://{$_SERVER['HTTP_HOST']}/"
));
define('TIMEZONE', getenv('TIMEZONE') ?: 'America/New_York');

// Database Configuration
define('DB_FILE', getenv('DB_FILE') ?: __DIR__ . '/db/cms.db');
define('DB_SCHEMA_VERSION', getenv('DB_SCHEMA_VERSION') ?: '1.0');

// API Configuration
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'https://api.abassetmanagement.com/api3/');
define('CLIENT_ID', getenv('CLIENT_ID') ?: '');
define('CLIENT_SECRET', getenv('CLIENT_SECRET') ?: '');
define('API_USERNAME', getenv('API_USERNAME') ?: '');
define('API_PASSWORD', getenv('API_PASSWORD') ?: '');
define('API_SCOPE', getenv('API_SCOPE') ?: '');

// Debugging
define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE') ?: false, FILTER_VALIDATE_BOOLEAN));
define('DEBUG_LOG_FILE', getenv('DEBUG_LOG_FILE') ?: __DIR__ . '/logs/debug.log');
define('MAX_LOG_SIZE_MB', (int)(getenv('MAX_LOG_SIZE_MB') ?: 10));

// Ensure timezone is set
date_default_timezone_set(TIMEZONE);

// Error reporting
error_reporting(DEBUG_MODE ? E_ALL : E_ERROR);
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', DEBUG_LOG_FILE);

// Security headers
if (!headers_sent()) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header_remove("X-Powered-By");
}