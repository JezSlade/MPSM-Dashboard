<?php
declare(strict_types=1);
/**
 * config.php – single‐point bootstrap for env, session, and constants.
 */

// ─── 0) Load .env (if available) so DEBUG_MODE can be set early ───────────────
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

// ─── 1) DEBUG_MODE & Error Reporting ─────────────────────────────────────────
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors', DEBUG_MODE ? '1' : '0');
error_reporting(E_ALL);

// ─── 2) Secure Session Startup ────────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 3) Base URLs & App Name ───────────────────────────────────────────────────
define('APP_NAME',        $_ENV['APP_NAME']        ?? 'MPSM Dashboard');
define('SITE_BASE_URL',   rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
define('API_BASE_URL',    rtrim($_ENV['API_BASE_URL']  ?? 'https://api.abassetmanagement.com/api3/', '/') . '/');
// Back‐compat for any legacy BASE_URL usage in views/partials:
define('BASE_URL',        SITE_BASE_URL);

// ─── 4) Credentials & Dealer Info ─────────────────────────────────────────────
define('CLIENT_ID',       $_ENV['CLIENT_ID']       ?? '');
define('CLIENT_SECRET',   $_ENV['CLIENT_SECRET']   ?? '');
define('USERNAME',        $_ENV['USERNAME']        ?? '');
define('PASSWORD',        $_ENV['PASSWORD']        ?? '');
define('SCOPE',           $_ENV['SCOPE']           ?? '');
define('TOKEN_URL',       $_ENV['TOKEN_URL']       ?? '');
define('DEALER_CODE',     $_ENV['DEALER_CODE']     ?? '');
define('DEALER_ID',       $_ENV['DEALER_ID']       ?? '');
define('DEVICE_PAGE_SIZE',(int) ($_ENV['DEVICE_PAGE_SIZE'] ?? 50));

// ─── 5) Validate Critical Config ──────────────────────────────────────────────
if (!CLIENT_ID || !CLIENT_SECRET || !DEALER_CODE) {
    if (DEBUG_MODE) {
        throw new RuntimeException('Missing essential .env variables (CLIENT_ID, CLIENT_SECRET, or DEALER_CODE).');
    }
    // In production, just log and continue; API calls will fail gracefully.
    error_log('⚠️ config.php: Missing CLIENT_ID, CLIENT_SECRET, or DEALER_CODE in .env');
}

// End of config.php
