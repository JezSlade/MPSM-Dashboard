<?php
// includes/logger.php
// Unified backend logger routing to Debug Info Stream widget

if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', __DIR__ . '/../widgets/debug_info.php');
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
        $log_file = __DIR__ . '/../widgets/debug_info.php/debug_log.txt';
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > 120) {
            $lines = array_slice($lines, -100);
            file_put_contents($log_file, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
        }
    }
}
?>
