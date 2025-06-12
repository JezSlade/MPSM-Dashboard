<?php
/**
 * Application Configuration
 *
 * This file centralizes application-wide configuration settings.
 * For a production environment, these values should ideally come from
 * environment variables or a secure configuration management system.
 */

// Define application base URL
if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/');
}

// Debugging and Logging Configuration
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('DEBUG_PANEL_ENABLED')) {
    define('DEBUG_PANEL_ENABLED', filter_var(getenv('DEBUG_PANEL_ENABLED'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('DEBUG_LOG_TO_FILE')) {
    define('DEBUG_LOG_TO_FILE', filter_var(getenv('DEBUG_LOG_TO_FILE'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('MAX_DEBUG_LOG_SIZE_MB')) {
    define('MAX_DEBUG_LOG_SIZE_MB', (int)getenv('MAX_DEBUG_LOG_SIZE_MB') ?: 5);
}

// Log Level Configuration
if (!defined('LOG_INFO')) {
    define('LOG_INFO', filter_var(getenv('LOG_INFO'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('LOG_WARNING')) {
    define('LOG_WARNING', filter_var(getenv('LOG_WARNING'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('LOG_ERROR')) {
    define('LOG_ERROR', filter_var(getenv('LOG_ERROR'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('LOG_DEBUG')) {
    define('LOG_DEBUG', filter_var(getenv('LOG_DEBUG'), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('LOG_SECURITY')) {
    define('LOG_SECURITY', filter_var(getenv('LOG_SECURITY'), FILTER_VALIDATE_BOOLEAN));
}

// Application Version
if (!defined('APP_VERSION')) {
    define('APP_VERSION', getenv('APP_VERSION') ?: '0.0.0');
}

// Application Name (New)
if (!defined('APP_NAME')) {
    define('APP_NAME', getenv('APP_NAME') ?: 'Application');
}

// Timezone
if (!defined('TIMEZONE')) {
    define('TIMEZONE', getenv('TIMEZONE') ?: 'America/New_York');
}
date_default_timezone_set(TIMEZONE);

// Define log file path
if (!defined('LOG_FILE_PATH')) {
    define('LOG_FILE_PATH', __DIR__ . '/../debug.log');
}