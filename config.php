<?php
/**
 * MPSM Dashboard - Configuration File
 *
 * This file contains all global configuration settings for the MPSM Dashboard.
 * It's the central place to manage environment variables, paths, debug modes,
 * and other essential settings that control the application's behavior.
 *
 * All settings are heavily commented to provide clear understanding of their purpose.
 *
 * Debugging Philosophy:
 * Every configuration option related to debugging should be clearly defined here.
 * If something isn't working, the first place to check should be the debug settings.
 */

// --- General Application Settings ---

/**
 * @var string APP_NAME
 * A user-friendly name for the application. Used in titles and headers.
 */
define('APP_NAME', 'MPSM Dashboard');

/**
 * @var string APP_VERSION
 * Current version of the dashboard. Useful for caching busting and debugging.
 */
define('APP_VERSION', '0.1.0-alpha');

/**
 * @var string APP_BASE_PATH
 * The absolute file path to the root directory of the application.
 * This is crucial for including files securely and reliably, regardless of
 * where the script is executed or the URL it's accessed from.
 * Using __DIR__ ensures this path is always relative to this config file itself.
 */
define('APP_BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR); // Ensures trailing slash for consistent path concatenation.

// --- Debugging Settings ---

/**
 * @var bool DEBUG_MODE
 * Enables or disables the global debug mode for the application.
 *
 * When true:
 * - Detailed error messages might be displayed (if error_reporting is also configured).
 * - The debug panel will be visible (unless specifically hidden by other settings).
 * - More verbose logging will occur.
 * - Performance might be slightly impacted due to increased logging and checks.
 *
 * When false:
 * - Production-ready mode. Errors should be logged but not displayed to users.
 * - Debug panel will be hidden.
 * - Minimal logging for performance.
 *
 * ALWAYS set to false in a production environment for security and performance.
 */
define('DEBUG_MODE', true);

/**
 * @var bool DEBUG_PANEL_ENABLED
 * Controls the visibility of the dedicated debug panel in the UI.
 * This can be independent of DEBUG_MODE. For instance, DEBUG_MODE might be true
 * (for logging) but the panel hidden for a cleaner UI during testing.
 */
define('DEBUG_PANEL_ENABLED', true);

/**
 * @var string DEBUG_LOG_FILE
 * The path to the file where debug messages will be logged.
 * Ensure this directory is writable by the web server.
 * If null or empty, logging might be directed to PHP's error log or stdout
 * depending on the `debug_log_to_file` setting.
 */
define('DEBUG_LOG_FILE', APP_BASE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'debug.log');

/**
 * @var bool DEBUG_LOG_TO_FILE
 * If true, debug messages will be written to DEBUG_LOG_FILE.
 * If false, debug messages will only be echoed to the debug panel (if enabled).
 */
define('DEBUG_LOG_TO_FILE', true);

/**
 * @var int MAX_DEBUG_LOG_SIZE_MB
 * Maximum size of the debug log file in megabytes before it's truncated or rotated.
 * Set to 0 for no size limit (not recommended for long-running applications).
 */
define('MAX_DEBUG_LOG_SIZE_MB', 5);

/**
 * @var array DEBUG_LOG_LEVELS
 * Defines the types of messages that will be logged.
 * Each level (e.g., 'INFO', 'WARNING', 'ERROR') can be enabled/disabled.
 * This allows fine-grained control over what gets written to the log.
 */
define('DEBUG_LOG_LEVELS', [
    'INFO'    => true,    // General informational messages
    'WARNING' => true,    // Non-critical issues
    'ERROR'   => true,    // Critical errors that prevent functionality
    'DEBUG'   => true,    // Very verbose messages, useful for tracing execution flow
    'SECURITY' => true    // Security-related events (e.g., failed logins, suspicious activity)
]);

// --- Paths Configuration ---

/**
 * @var string BASE_URL
 * The base URL for the application. Used for generating absolute URLs
 * for CSS, JS, images, etc. If the app is in a subdirectory (e.g., example.com/dashboard),
 * this should be set accordingly. For root, use '/'.
 * Automatically determined if possible, but can be overridden.
 */
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // Get the current directory path relative to the document root
define('BASE_URL', $protocol . $host . $uri . '/');

/**
 * @var string CSS_PATH
 * Relative path to the CSS directory.
 */
define('CSS_PATH', 'css/');

/**
 * @var string JS_PATH
 * Relative path to the JavaScript directory.
 */
define('JS_PATH', 'js/');

/**
 * @var string VIEWS_PATH
 * Absolute path to the directory containing view files.
 * This helps prevent directory traversal vulnerabilities and ensures correct file inclusion.
 */
define('VIEWS_PATH', APP_BASE_PATH . 'views' . DIRECTORY_SEPARATOR);

/**
 * @var string CARDS_PATH
 * Absolute path to the directory containing individual card files.
 * This is where the dashboard will look for discoverable card components.
 */
define('CARDS_PATH', APP_BASE_PATH . 'cards' . DIRECTORY_SEPARATOR);

/**
 * @var string INCLUDES_PATH
 * Absolute path to the directory containing common includes.
 */
define('INCLUDES_PATH', APP_BASE_PATH . 'includes' . DIRECTORY_SEPARATOR);

// --- Database Configuration (Placeholders) ---
// Note: Actual credentials should ideally be stored outside the web root
// and loaded via environment variables or a secure configuration management system.
// For this skeleton, we'll keep them here as placeholders.

/**
 * @var string DB_HOST
 * Database host.
 */
define('DB_HOST', 'localhost');

/**
 * @var string DB_NAME
 * Database name.
 */
define('DB_NAME', 'mpsm_dashboard_db');

/**
 * @var string DB_USER
 * Database user.
 */
define('DB_USER', 'db_user');

/**
 * @var string DB_PASS
 * Database password.
 */
define('DB_PASS', 'db_password');

// --- MPS Monitor API Configuration (Placeholders) ---
// These are illustrative and will need to be replaced with actual API keys/secrets
// once you integrate with the MPS Monitor API.

/**
 * @var string MPSM_API_KEY
 * Your MPS Monitor API Key.
 */
define('MPSM_API_KEY', 'your_mps_monitor_api_key_here');

/**
 * @var string MPSM_API_SECRET
 * Your MPS Monitor API Secret.
 */
define('MPSM_API_SECRET', 'your_mps_monitor_api_secret_here');

/**
 * @var string MPSM_API_BASE_URL
 * The base URL for the MPS Monitor API.
 * This might vary based on your region or API version.
 */
define('MPSM_API_BASE_URL', 'https://api.mpsmonitor.com/v1/'); // Example URL

// --- Error Handling and Reporting ---

/**
 * Set PHP error reporting level.
 * E_ALL: Report all errors, warnings, and notices.
 * E_ALL & ~E_NOTICE & ~E_STRICT: All errors except notices and strict standards warnings.
 *
 * In production, you typically set this to 0 and rely on logging.
 */
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On'); // Display errors in development/debug
} else {
    error_reporting(0); // No errors displayed to the user
    ini_set('display_errors', 'Off');
}

/**
 * Set default timezone to avoid date/time errors.
 * IMPORTANT: Choose the timezone relevant to your server or target audience.
 */
date_default_timezone_set('America/New_York'); // Example: New York timezone

// --- Session Management ---
// Start a session to store user-specific data, like current view, customer selection, etc.
// Ensure session_start() is called only once per request.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the logs directory exists
if (DEBUG_LOG_TO_FILE && !is_dir(dirname(DEBUG_LOG_FILE))) {
    mkdir(dirname(DEBUG_LOG_FILE), 0755, true);
    // Log this action if possible, though it's a bootstrap step.
}

// Pre-flight check for configurations. This is a basic sanity check.
if (DEBUG_MODE) {
    if (!defined('APP_BASE_PATH') || !is_dir(APP_BASE_PATH)) {
        trigger_error("Configuration error: APP_BASE_PATH is not defined or is not a valid directory.", E_USER_ERROR);
    }
    if (!defined('VIEWS_PATH') || !is_dir(VIEWS_PATH)) {
        trigger_error("Configuration error: VIEWS_PATH is not defined or is not a valid directory. Expected: " . VIEWS_PATH, E_USER_ERROR);
    }
    if (!defined('CARDS_PATH') || !is_dir(CARDS_PATH)) {
        trigger_error("Configuration error: CARDS_PATH is not defined or is not a valid directory. Expected: " . CARDS_PATH, E_USER_ERROR);
    }
    if (!defined('INCLUDES_PATH') || !is_dir(INCLUDES_PATH)) {
        trigger_error("Configuration error: INCLUDES_PATH is not defined or is not a valid directory. Expected: " . INCLUDES_PATH, E_USER_ERROR);
    }
}

// End of config.php