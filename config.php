<?php
declare(strict_types=1);
/**
 * config.php – single‐point bootstrap for environment, error reporting,
 *               session, and application constants, with no external library dependencies.
 *
 * Patches applied:
 *  - Added DEBUG_PANEL_ENABLED constant (from .env or default = false).
 *  - Added DEBUG_LOG_TO_FILE and MAX_DEBUG_LOG_SIZE_MB flags for future use.
 *  - Manual .env parsing ensures $_ENV contains DEBUG_PANEL_ENABLED. :contentReference[oaicite:3]{index=3}
 *  - Full E_ALL reporting always on for dev.
 */

// ─── 1) EARLY ERROR REPORTING ─────────────────────────────────────────────────
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 2) MANUAL .env PARSING ───────────────────────────────────────────────────
$envPath = __DIR__ . '/.env';
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// ─── 3) DEBUG FLAGS ───────────────────────────────────────────────────────────
// Turn debug mode on/off
define('DEBUG_MODE',
    filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
);

// Show or hide the debug‐panel in your footer. :contentReference[oaicite:4]{index=4}
define('DEBUG_PANEL_ENABLED',
    filter_var($_ENV['DEBUG_PANEL_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
);

// If you later decide to log debug messages to file:
define('DEBUG_LOG_TO_FILE',
    filter_var($_ENV['DEBUG_LOG_TO_FILE'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
);

// Max size in MB for rotating debug log files:
define('MAX_DEBUG_LOG_SIZE_MB',
    (int) ($_ENV['MAX_DEBUG_LOG_SIZE_MB'] ?? 5)
);

// ─── 4) RE-APPLY ERROR REPORTING BASED ON DEBUG_MODE ─────────────────────────
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 5) SECURE SESSION STARTUP ──────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 6) APPLICATION CONSTANTS ────────────────────────────────────────────────
define('APP_NAME',      $_ENV['APP_NAME']      ?? 'MPSM Dashboard');
define('APP_VERSION',   $_ENV['APP_VERSION']   ?? '1.0.0');
define('SITE_BASE_URL', rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
define('API_BASE_URL',  rtrim(
    $_ENV['API_BASE_URL'] ?? 'https://api.abassetmanagement.com/api3/',
    '/'
) . '/');
define('BASE_URL', SITE_BASE_URL); // legacy alias

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

// End of config.php – no closing PHP tag to prevent accidental output
