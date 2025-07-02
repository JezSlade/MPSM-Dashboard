<?php // mps_monitor/config/mps_config.php
declare(strict_types=1);

/**
 * MPS Monitor-specific configuration values.
 * This file defines constants for API credentials and token caching.
 * It is required at the top of all MPSMonitorClient and API endpoint files.
 */

// Define the base URL for the MPS Monitor API
define('MPS_API_BASE', 'https://api.abassetmanagement.com/api3/');

// Define OAuth 2.0 client credentials
define('MPS_API_CLIENT_ID', '9AT9j4UoU2BgLEqmiYCz');
define('MPS_API_SECRET', '9gTbAKBCZe1ftYQbLbq9');

// Define the username and password for the password grant type
define('MPS_API_USERNAME', 'dashboard');
define('MPS_API_PASSWORD', 'd@$hpa$$2024');

// Define the scope for the OAuth token
define('MPS_API_SCOPE', 'account');

// Define the token URL
define('MPS_TOKEN_URL', 'https://api.abassetmanagement.com/api3/token');

// Define the path for the token cache file (for MPSMonitorClient to use)
// This should be outside the web-accessible directory for security.
// It's placed two levels up from this config file, in a 'cache' directory.
define('MPS_TOKEN_CACHE_FILE', __DIR__ . '/../../.token_cache.json');

// Default cache TTL for API responses in seconds (e.g., 5 minutes)
define('DEFAULT_CACHE_TTL', 300);

// --- Debugging and Logging Configuration (from .env) ---
// These are defined as constants to make them globally accessible.
define('DEBUG_MODE', true); // Set to false in production
define('DEBUG_LOG_TO_FILE', true); // Set to false in production for security/performance
define('MAX_DEBUG_LOG_SIZE_MB', 10); // Max log file size in MB
define('LOG_INFO', true);
define('LOG_WARNING', true);
define('LOG_ERROR', true);
define('LOG_DEBUG', true);
define('LOG_SECURITY', true);

// Ensure the log directory exists
if (DEBUG_LOG_TO_FILE) {
    // Assuming a 'logs' directory parallel to 'mps_monitor'
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true); // Create directory with read/write/execute for owner, read/execute for group/others
    }
    // Set PHP's error_log directive to our custom log file
    ini_set('error_log', $logDir . '/debug.log');
}

// Set PHP error reporting and display based on DEBUG_MODE
if (DEBUG_MODE === true) {
    error_reporting(E_ALL); // Report all PHP errors
    ini_set('display_errors', '1'); // Display errors in browser (for development)
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT); // Report all errors except notices, deprecated, strict
    ini_set('display_errors', '0'); // Do not display errors in browser (for production)
}

/**
 * Custom logging function.
 * Logs messages to the configured error log file with a timestamp and level.
 *
 * @param string $message The message to log.
 * @param string $level The log level (e.g., 'INFO', 'WARNING', 'ERROR', 'DEBUG', 'SECURITY').
 */
function custom_log(string $message, string $level = 'INFO'): void
{
    // Check if logging to file is explicitly disabled by the constant.
    // Since DEBUG_LOG_TO_FILE is defined in this file, we don't need defined() check.
    if (!DEBUG_LOG_TO_FILE) { // This condition will be true only if DEBUG_LOG_TO_FILE is false.
        return; // This line is now reachable if DEBUG_LOG_TO_FILE is false.
    }

    $logFilePath = ini_get('error_log');
    // Fallback if error_log is not set or accessible
    if (!$logFilePath) {
        $logFilePath = __DIR__ . '/../../logs/debug.log';
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = sprintf("[%s] [%s] %s%s", $timestamp, strtoupper($level), $message, PHP_EOL);

    // Implement basic log file size management
    if (file_exists($logFilePath) && filesize($logFilePath) > (MAX_DEBUG_LOG_SIZE_MB * 1024 * 1024)) {
        // If the log file exceeds the max size, overwrite it to prevent it from growing indefinitely.
        // In a production system, you might implement log rotation instead.
        file_put_contents($logFilePath, $logEntry);
    } else {
        // Append the log entry to the file
        file_put_contents($logFilePath, $logEntry, FILE_APPEND);
    }
}
?>
