<?php
declare(strict_types=1);
/**
 * config.php
 *
 * - E_ALL error reporting
 * - session_start() before output
 * - Conditional .env loading via phpdotenv
 * - Single definitions of all required constants
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

if (! defined('CLIENT_ID')) {
    define('CLIENT_ID', $_ENV['CLIENT_ID'] ?? '');
}
if (! defined('CLIENT_SECRET')) {
    define('CLIENT_SECRET', $_ENV['CLIENT_SECRET'] ?? '');
}
if (! defined('USERNAME')) {
    define('USERNAME', $_ENV['USERNAME'] ?? '');
}
if (! defined('PASSWORD')) {
    define('PASSWORD', $_ENV['PASSWORD'] ?? '');
}
if (! defined('SCOPE')) {
    define('SCOPE', $_ENV['SCOPE'] ?? '');
}
if (! defined('TOKEN_URL')) {
    define('TOKEN_URL', $_ENV['TOKEN_URL'] ?? '');
}
if (! defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'https://api.abassetmanagement.com/api3/');
}
if (! defined('DEALER_CODE')) {
    define('DEALER_CODE', $_ENV['DEALER_CODE'] ?? '');
}
if (! defined('DEALER_ID')) {
    define('DEALER_ID', $_ENV['DEALER_ID'] ?? '');
}
if (! defined('DEVICE_PAGE_SIZE')) {
    define('DEVICE_PAGE_SIZE', (int) ($_ENV['DEVICE_PAGE_SIZE'] ?? 50));
}
// Turn debug logging on/off. Defaults to false unless you set DEBUG_MODE=true in .env
if (! defined('DEBUG_MODE')) {
    define(
        'DEBUG_MODE',
        filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
    );
}

// End of config.php
