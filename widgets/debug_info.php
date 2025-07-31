<?php
// widgets/debug_info.php

// Widget Name: Debug Info Stream
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

$_widget_config = [
    'name' => 'Debug Info Stream',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', __DIR__ . '/debug_log.txt');
}
if (!defined('MAX_LOG_LINES')) {
    define('MAX_LOG_LINES', 100);
}

if (!function_exists('append_debug_log')) {
    function append_debug_log(string $message, string $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        file_put_contents(DEBUG_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);

        $lines = file(DEBUG_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > MAX_LOG_LINES * 1.2) {
            $lines = array_slice($lines, -MAX_LOG_LINES);
            file_put_contents(DEBUG_LOG_FILE, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
        }
    }
}

function get_debug_log_content(): string {
    if (!file_exists(DEBUG_LOG_FILE)) {
        return "";
    }
    return file_get_contents(DEBUG_LOG_FILE);
}

// Render the log content inside a scrollable <pre>
echo "<div style='height:100%; overflow:auto; padding:10px;'>";
echo "<pre style='font-size:0.9em; color:#ccc;'>" . htmlspecialchars(get_debug_log_content()) . "</pre>";
echo "</div>";
fvads