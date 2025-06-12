<?php
declare(strict_types=1);
/**
 * config.php – single-point bootstrap for env, session, and constants.
 *
 * Patches applied:
 *  1. **Early error reporting** so even bootstrap errors are visible.
 *  2. **No closing `?>`** to prevent accidental whitespace.
 *  3. **Environment loading** guarded by file_exists.
 *  4. **Strict session settings** for security.
 *  5. **Critical‐vars check** throws in DEBUG_MODE, logs otherwise.
 *
 * @file
 */

// ─── 0) EARLY ERROR REPORTING ───────────────────────────────────────────────────
// Always report everything, immediately. This kicks in before Dotenv/autoload.
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 1) Load .env (if available) so DEBUG_MODE can be set early ──────────────
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    // Load environment variables into $_ENV
    \Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

// ─── 2) DEBUG_MODE & Error Reporting Override ─────────────────────────────────
// DEBUG_MODE from .env; if not set, default to false.
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

// In case someone turns off display_errors, we still want full reporting
ini_set('display_errors',        DEBUG_MODE ? '1' : '1');
ini_set('display_startup_errors',DEBUG_MODE ? '1' : '1');
error_reporting(E_ALL);

// ─── 3) Secure Session Startup ────────────────────────────────────────────────
session_set_cookie_params([
    'httponly' => true,
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 4) Base URLs & App Name ──────────────────────────────────────────────────
define('APP_NAME',      $_ENV['APP_NAME']        ?? 'MPSM Dashboard');
define('SITE_BASE_URL', rtrim($_ENV['SITE_BASE_URL'] ?? '/', '/') . '/');
define('API_BASE_URL',  rtrim($_ENV['API_BASE_URL']  ?? 'https://api.abassetmanagement.com/api3/', '/') . '/');
// Legacy backward-compat
define('BASE_URL', SITE_BASE_URL);

// ─── 5) Credentials & Dealer Info ─────────────────────────────────────────────
define('CLIENT_ID',       $_ENV['CLIENT_ID']       ?? '');
define('CLIENT_SECRET',   $_ENV['CLIENT_SECRET']   ?? '');
define('USERNAME',        $_ENV['USERNAME']        ?? '');
define('PASSWORD',        $_ENV['PASSWORD']        ?? '');
define('SCOPE',           $_ENV['SCOPE']           ?? '');
define('TOKEN_URL',       $_ENV['TOKEN_URL']       ?? '');
define('DEALER_CODE',     $_ENV['DEALER_CODE']     ?? '');
define('DEALER_ID',       $_ENV['DEALER_ID']       ?? '');
define('DEVICE_PAGE_SIZE', (int) ($_ENV['DEVICE_PAGE_SIZE'] ?? 50));

// ─── 6) Validate Critical Config ─────────────────────────────────────────────
if (!CLIENT_ID || !CLIENT_SECRET || !DEALER_CODE) {
    $msg = 'Missing essential .env variables: CLIENT_ID, CLIENT_SECRET, or DEALER_CODE.';
    if (DEBUG_MODE) {
        throw new RuntimeException($msg);
    }
    error_log("⚠️ config.php: $msg");
}

// End of file – no closing PHP tag to avoid accidental output
