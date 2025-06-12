<?php
declare(strict_types=1);
/**
 * config.php – single-point bootstrap for environment, error reporting, session,
 *               and application constants, with no external library dependencies.
 *
 * Changes:
 *  - Removed dependency on phpdotenv; implemented manual .env parsing.
 *  - Always enabled E_ALL error reporting.
 *  - Secure session cookie settings.
 *  - Defined all required constants from .env (or sensible defaults).
 *  - Validates essential credentials and logs or throws on missing values.
 */

// ─── 1) EARLY ERROR REPORTING ─────────────────────────────────────────────────
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 2) MANUAL .env PARSING ───────────────────────────────────────────────────
// Read .env file line by line, skip empty lines and comments, split on first '='
$envPath = __DIR__ . '/.env';
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        // Skip blank lines and comments
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        // Populate $_ENV so we can use filter_input or $_ENV directly
        $_ENV[trim($name)] = trim($value);
    }
}

// ─── 3) DEBUG_MODE FLAG ──────────────────────────────────────────────────────
// Determine whether debug mode is on (true/false)
define(
    'DEBUG_MODE',
    filter_var(
        $_ENV['DEBUG_MODE'] ?? 'false',
        FILTER_VALIDATE_BOOLEAN
    )
);

// ─── 4) RE-APPLY ERROR REPORTING BASED ON DEBUG_MODE ─────────────────────────
// We keep E_ALL on regardless; DEBUG_MODE controls display_errors if needed.
ini_set('display_errors',        DEBUG_MODE ? '1' : '1');
ini_set('display_startup_errors',DEBUG_MODE ? '1' : '1');
error_reporting(E_ALL);

// ─── 5) SECURE SESSION STARTUP ──────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'secure'   => (
        !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] === 'on'
    ),
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 6) APPLICATION CONSTANTS ────────────────────────────────────────────────
// Basic app info
define('APP_NAME',      $_ENV['APP_NAME']      ?? 'MPSM Dashboard');
define('SITE_BASE_URL', rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
define('API_BASE_URL',  rtrim($_ENV['API_BASE_URL']
    ?? 'https://api.abassetmanagement.com/api3/', '/') . '/');
// Legacy alias
define('BASE_URL', SITE_BASE_URL);

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
// Identify any missing critical vars
$missing = [];
if (!CLIENT_ID)     $missing[] = 'CLIENT_ID';
if (!CLIENT_SECRET) $missing[] = 'CLIENT_SECRET';
if (!DEALER_CODE)   $missing[] = 'DEALER_CODE';

if (!empty($missing)) {
    $msg = 'Missing essential .env variables: ' . implode(', ', $missing);
    if (DEBUG_MODE) {
        // In debug mode, halt execution so you catch it immediately
        throw new RuntimeException($msg);
    }
    // Otherwise, log a warning and allow the app to continue (will likely error later)
    error_log("⚠️ config.php warning: {$msg}");
}

// ─── End of config.php – no closing PHP tag to prevent accidental output ─────
