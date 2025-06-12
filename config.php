<?php
declare(strict_types=1);
/**
 * config.php – single‐point bootstrap for environment, error reporting, session,
 *               and application constants, with no external library dependencies.
 *
 * Changes in this patch:
 *  - Added APP_VERSION constant (from .env or fallback) so footer.php can display it.
 *  - Kept manual .env parsing and rigorous error reporting.
 */

// ─── 1) EARLY ERROR REPORTING ─────────────────────────────────────────────────
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 2) MANUAL .env PARSING ───────────────────────────────────────────────────
// Read .env line-by-line, skip comments/empty, split on first '='
$envPath = __DIR__ . '/.env';
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// ─── 3) DEBUG_MODE FLAG ──────────────────────────────────────────────────────
define(
    'DEBUG_MODE',
    filter_var(
        $_ENV['DEBUG_MODE'] ?? 'false',
        FILTER_VALIDATE_BOOLEAN
    )
);

// ─── 4) RE‐APPLY ERROR REPORTING BASED ON DEBUG_MODE ─────────────────────────
ini_set('display_errors',        '1'); // always on for dev
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 5) SECURE SESSION STARTUP ──────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'secure'   => (
        !empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    ),
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 6) APPLICATION CONSTANTS ────────────────────────────────────────────────
// Basic app info
define('APP_NAME',      $_ENV['APP_NAME']      ?? 'MPSM Dashboard');
define('APP_VERSION',   $_ENV['APP_VERSION']   ?? '1.0.0');       // ← New!
define('SITE_BASE_URL', rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
define('API_BASE_URL',  rtrim(
    $_ENV['API_BASE_URL'] ?? 'https://api.abassetmanagement.com/api3/',
    '/'
) . '/');
define('BASE_URL', SITE_BASE_URL);  // Legacy alias

// OAuth2 / API credentials
define('CLIENT_ID',       $_ENV['CLIENT_ID']       ?? '');
define('CLIENT_SECRET',   $_ENV['CLIENT_SECRET']   ?? '');
define('USERNAME',        $_ENV['USERNAME']        ?? '');
define('PASSWORD',        $_ENV['PASSWORD']        ?? '');
define('SCOPE',           $_ENV['SCOPE']           ?? '');
define('TOKEN_URL',       $_ENV['TOKEN_URL']       ?? '');

// Dealer configuration
define('DEALER_CODE',     $_ENV['DEALER_CODE']     ?? '');
define('DEALER_ID',       $_ENV['DEALER_ID']       ?? '');

// Pagination default
define('DEVICE_PAGE_SIZE', (int) ($_ENV['DEVICE_PAGE_SIZE'] ?? 50));

// ─── 7) VALIDATE ESSENTIAL CONFIG ───────────────────────────────────────────
// Check for missing critical vars
$missing = [];
if (!CLIENT_ID)     $missing[] = 'CLIENT_ID';
if (!CLIENT_SECRET) $missing[] = 'CLIENT_SECRET';
if (!DEALER_CODE)   $missing[] = 'DEALER_CODE';

if (!empty($missing)) {
    $msg = 'Missing essential .env variables: ' . implode(', ', $missing);
    if (DEBUG_MODE) {
        throw new RuntimeException($msg);
    }
    error_log("⚠️ config.php warning: {$msg}");
}

// ─── End of config.php – no closing PHP tag ──────────────────────────────────
