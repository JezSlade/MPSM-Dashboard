<?php
declare(strict_types=1);
/**
 * config.php - single source of truth for env, session, and constants.
 */

// 1) Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 2) Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3) Conditionally load Dotenv
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

// 4) Site vs API Base URLs
defined('SITE_BASE_URL') or define('SITE_BASE_URL', rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
defined('API_BASE_URL')  or define('API_BASE_URL', rtrim($_ENV['API_BASE_URL']  ?? 'https://api.abassetmanagement.com/api3/', '/') . '/');

// 5) Credentials & Dealer Info
defined('CLIENT_ID')     or define('CLIENT_ID', $_ENV['CLIENT_ID'] ?? '');
defined('CLIENT_SECRET') or define('CLIENT_SECRET', $_ENV['CLIENT_SECRET'] ?? '');
defined('USERNAME')      or define('USERNAME', $_ENV['USERNAME'] ?? '');
defined('PASSWORD')      or define('PASSWORD', $_ENV['PASSWORD'] ?? '');
defined('SCOPE')         or define('SCOPE', $_ENV['SCOPE'] ?? '');
defined('TOKEN_URL')     or define('TOKEN_URL', $_ENV['TOKEN_URL'] ?? '');
defined('DEALER_CODE')   or define('DEALER_CODE', $_ENV['DEALER_CODE'] ?? '');
defined('DEALER_ID')     or define('DEALER_ID', $_ENV['DEALER_ID'] ?? '');

// 6) Debug & Page Size
defined('DEBUG_MODE')       or define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
defined('DEVICE_PAGE_SIZE') or define('DEVICE_PAGE_SIZE', (int) ($_ENV['DEVICE_PAGE_SIZE'] ?? 50));

// End of config.php
