<?php
/**
 * config.php
 *
 * 1) Loads key=value from .env
 * 2) Defines constants from .env or sensible defaults
 * 3) Auto-detects APP_VERSION
 * 4) Sets BASE_URL, paths, debug, and API endpoint
 */

// 1) Parse .env (no PHP tags inside .env!)
$envFile = __DIR__ . '/.env';
if (! file_exists($envFile) || ! is_readable($envFile)) {
    throw new RuntimeException("Cannot load .env at {$envFile}");
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    list($key, $val) = explode('=', $line, 2);
    $env[trim($key)] = trim($val);
}

// Helper to define constants
function define_env(string $key, $default = ''): void {
    global $env;
    $val = array_key_exists($key, $env) ? $env[$key] : $default;
    define($key, $val);
}

// 2) Environment-backed constants
define_env('CLIENT_ID');
define_env('CLIENT_SECRET');
define_env('USERNAME');
define_env('PASSWORD');
define_env('SCOPE');
define_env('TOKEN_URL');
define_env('DEALER_CODE');
define_env('DEALER_ID');
define_env('BASE_URL'); // optional override

// 3) APP_NAME + APP_VERSION
define_env('APP_NAME', 'MPSM Dashboard');
$version = '';
$versionJs = __DIR__ . '/version.js';
if (file_exists($versionJs)) {
    $js = file_get_contents($versionJs);
    if (preg_match('/version\s*[:=]\s*[\'"]([^\'"]+)[\'"]/', $js, $m)) {
        $version = $m[1];
    }
}
if (!$version && ! empty($env['APP_VERSION'])) {
    $version = $env['APP_VERSION'];
}
if (!$version) {
    $version = date('YmdHis');
}
define('APP_VERSION', $version);

// 4) Paths
define('APP_BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('CSS_PATH',      'css/');
define('JS_PATH',       'js/');
define('VIEWS_PATH',    APP_BASE_PATH . 'views' . DIRECTORY_SEPARATOR);
define('CARDS_PATH',    APP_BASE_PATH . 'cards' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', APP_BASE_PATH . 'includes' . DIRECTORY_SEPARATOR);

// 5) Debug settings
define('DEBUG_MODE',          filter_var($env['DEBUG_MODE']          ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('DEBUG_PANEL_ENABLED', filter_var($env['DEBUG_PANEL_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('DEBUG_LOG_FILE',      APP_BASE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'debug.log');
define('DEBUG_LOG_TO_FILE',   filter_var($env['DEBUG_LOG_TO_FILE']   ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('MAX_DEBUG_LOG_SIZE_MB', intval($env['MAX_DEBUG_LOG_SIZE_MB'] ?? 5));
define('DEBUG_LOG_LEVELS', [
    'INFO'     => filter_var($env['LOG_INFO']     ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'WARNING'  => filter_var($env['LOG_WARNING']  ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'ERROR'    => filter_var($env['LOG_ERROR']    ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'DEBUG'    => filter_var($env['LOG_DEBUG']    ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'SECURITY' => filter_var($env['LOG_SECURITY'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
]);

// 6) API Base URL
define('MPSM_API_BASE_URL', $env['MPSM_API_BASE_URL'] ?? 'https://api.abassetmanagement.com/api3/');

// 7) Auto BASE_URL if not provided
if (!defined('BASE_URL') || BASE_URL === '') {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https://' : 'http://';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri   = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');
    define('BASE_URL', $proto . $host . $uri . '/');
}

// 8) Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
} else {
    error_reporting(0);
    ini_set('display_errors', 'Off');
}

// 9) Timezone
date_default_timezone_set($env['TIMEZONE'] ?? 'America/New_York');

// 10) Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 11) Ensure logs dir exists
if (DEBUG_LOG_TO_FILE) {
    $logDir = dirname(DEBUG_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
}
