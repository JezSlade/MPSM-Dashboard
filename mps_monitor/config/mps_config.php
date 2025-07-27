<?php // mps_monitor/config/mps_config.php
declare(strict_types=1);
// ✅ Enable detailed PHP error reporting for debugging (remove or disable in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
/**
 * MPS Monitor-specific configuration values.
 * Restored original structure with !defined() guards added only where necessary
 */

// Define the base URL for the MPS Monitor API
if (!defined('MPS_API_BASE')) {
    define('MPS_API_BASE', 'https://api.abassetmanagement.com/api3/');
}

// Define OAuth 2.0 client credentials
if (!defined('MPS_API_CLIENT_ID')) {
    define('MPS_API_CLIENT_ID', '9AT9j4UoU2BgLEqmiYCz');
}
if (!defined('MPS_API_SECRET')) {
    define('MPS_API_SECRET', '9gTbAKBCZe1ftYQbLbq9');
}

// Define the username and password for the password grant type
if (!defined('MPS_API_USERNAME')) {
    define('MPS_API_USERNAME', 'dashboard');
}
if (!defined('MPS_API_PASSWORD')) {
    define('MPS_API_PASSWORD', 'd@$hpa$$2024');
}

// Define the scope for the OAuth token
if (!defined('MPS_API_SCOPE')) {
    define('MPS_API_SCOPE', 'account');
}

// Define the token URL
if (!defined('MPS_TOKEN_URL')) {
    define('MPS_TOKEN_URL', 'https://api.abassetmanagement.com/api3/token');
}

// Define the path for the token cache file
if (!defined('MPS_TOKEN_CACHE_FILE')) {
    define('MPS_TOKEN_CACHE_FILE', __DIR__ . '/../../.token_cache.json');
}

// Default cache TTL for API responses in seconds
if (!defined('DEFAULT_CACHE_TTL')) {
    define('DEFAULT_CACHE_TTL', 300);
}

// --- Debugging and Logging Configuration ---
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}
if (!defined('DEBUG_LOG_TO_FILE')) {
    define('DEBUG_LOG_TO_FILE', true);
}
if (!defined('MAX_DEBUG_LOG_SIZE_MB')) {
    define('MAX_DEBUG_LOG_SIZE_MB', 10);
}
if (!defined('LOG_INFO')) {
    define('LOG_INFO', true);
}
if (!defined('LOG_WARNING')) {
    define('LOG_WARNING', true);
}
if (!defined('LOG_ERROR')) {
    define('LOG_ERROR', true);
}
if (!defined('LOG_DEBUG')) {
    define('LOG_DEBUG', true);
}
if (!defined('LOG_SECURITY')) {
    define('LOG_SECURITY', true);
}

// Ensure the log directory exists
if (DEBUG_LOG_TO_FILE) {
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/debug.log');
}

// Set PHP error reporting and display based on DEBUG_MODE
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

/**
 * Custom logging function.
 */
if (!function_exists('custom_log')) {
    function custom_log(string $message, string $level = 'INFO'): void
    {
        if (!DEBUG_LOG_TO_FILE) {
            return;
        }

        $logFilePath = ini_get('error_log') ?: __DIR__ . '/../../logs/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf("[%s] [%s] %s%s", $timestamp, strtoupper($level), $message, PHP_EOL);

        if (file_exists($logFilePath) && filesize($logFilePath) > (MAX_DEBUG_LOG_SIZE_MB * 1024 * 1024)) {
            file_put_contents($logFilePath, $logEntry);
        } else {
            file_put_contents($logFilePath, $logEntry, FILE_APPEND);
        }
    }
}
?>
