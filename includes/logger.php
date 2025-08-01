<?php
// includes/logger.php
// Unified backend logger routing to Debug Info Stream widget

// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', __DIR__ . '/../logs/debug.log');
}

function log_debug($msg) {
    append_debug_log($msg, 'DEBUG');
}

function log_info($msg) {
    append_debug_log($msg, 'INFO');
}

function log_error($msg) {
    append_debug_log($msg, 'ERROR');
}

// Ensure the function exists (may be defined already in widget context)
if (!function_exists('append_debug_log')) {
    function append_debug_log(string $message, string $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        $log_file = DEBUG_LOG_FILE;

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > 120) {
            $lines = array_slice($lines, -100);
            file_put_contents($log_file, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
        }
    }
}
