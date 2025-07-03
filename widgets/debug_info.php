<?php
// widgets/debug_info.php

// Widget Name: Debug Info Stream
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

// The $_widget_config array is no longer directly used for metadata extraction
// by discover_widgets(). It's kept here for backward compatibility or other
// internal widget logic if needed. The metadata is now parsed from comments.
$_widget_config = [
    'name' => 'Debug Info Stream',
    'icon' => 'bug', // This 'bug' will be overridden by the comment parsing
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Configuration for Debug Log File ---
// Define a temporary log file path. Ensure this directory is writable by the web server.
// For security and clarity, it's often good to place this outside the web root or in a dedicated logs folder.
// For this example, we'll place it in the same directory as the widget.
define('DEBUG_LOG_FILE', __DIR__ . '/debug_log.txt');
define('MAX_LOG_LINES', 100); // Limit the number of lines to display to prevent performance issues

/**
 * Appends a log message to the debug log file.
 * This function can be called from anywhere in your PHP code to add messages to the stream.
 * @param string $message The message to log.
 * @param string $level The log level (e.g., INFO, WARN, ERROR).
 */
function append_debug_log(string $message, string $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    // Append to file
    file_put_contents(DEBUG_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);

    // Optional: Trim log file if it gets too large
    $lines = file(DEBUG_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lines) > MAX_LOG_LINES * 1.2) { // Trim if it's 20% over the max
        $lines = array_slice($lines, -MAX_LOG_LINES);
        file_put_contents(DEBUG_LOG_FILE, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
    }
}

// Example usage (you can remove these or place them in other files for real logging)
// append_debug_log("Debug widget loaded.", "INFO");
// append_debug_log("User accessed dashboard.", "INFO");
// append_debug_log("Attempting to save settings.", "DEBUG");
// append_debug_log("Simulated error occurred!", "ERROR");


// --- Function to retrieve and format log content ---
function get_debug_log_content(): string {
    if (!file_exists(DEBUG_LOG_FILE)) {
        return '<p><em>Debug log file not found.</em></p>';
    }

    // Read all lines, reverse them to get newest first
    $lines = file(DEBUG_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // Newest log entries first
    $display_lines = array_slice($lines, 0, MAX_LOG_LINES); // Display only the latest N lines

    if (empty($display_lines)) {
        return '<p><em>Debug log is empty.</em></p>';
    }

    $output = '<pre style="background: var(--bg-primary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap; max-height: 200px; overflow-y: auto;">';
    foreach ($display_lines as $line) {
        // Basic highlighting for log levels
        if (strpos($line, '[ERROR]') !== false) {
            $output .= '<span style="color: var(--danger);">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
        } elseif (strpos($line, '[WARN]') !== false) {
            $output .= '<span style="color: var(--warning);">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
        } elseif (strpos($line, '[INFO]') !== false) {
            $output .= '<span style="color: var(--primary);">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
        } elseif (strpos($line, '[DEBUG]') !== false) {
            $output .= '<span style="color: var(--text-secondary);">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
        } else {
            $output .= htmlspecialchars($line) . PHP_EOL;
        }
    }
    $output .= '</pre>';
    return $output;
}

// Shared content for both views (time)
$time_content = '<h4 style="color: var(--accent); margin-top: 10px; margin-bottom: 5px;">Current Time:</h4>';
$time_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
$time_content .= htmlspecialchars(date('Y-m-d H:i:s'));
$time_content .= '</pre>';

// Session data content
$session_content = '<h4 style="color: var(--accent); margin-top: 10px; margin-bottom: 5px;">$_SESSION Data:</h4>';
$session_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
if (isset($_SESSION) && !empty($_SESSION)) {
    $display_session = $_SESSION;
    unset($display_session['PHPSESSID']);
    $session_content .= htmlspecialchars(print_r($display_session, true));
} else {
    $session_content .= 'Session is empty or not started.';
}
$session_content .= '</pre>';

// POST data content
$post_content = '<h4 style="color: var(--accent); margin-top: 20px; margin-bottom: 5px;">$_POST Data (Last Request):</h4>';
$post_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
if (isset($_POST) && !empty($_POST)) {
    $post_content .= htmlspecialchars(print_r($_POST, true));
} else {
    $post_content .= 'No POST data received in the last request.';
}
$post_content .= '</pre>';

// Debug Log Stream content
$debug_log_stream_content = '<h4 style="color: var(--accent); margin-top: 20px; margin-bottom: 5px;">Debug Log Stream (Newest First):</h4>';
$debug_log_stream_content .= get_debug_log_content();

?>
<div style="font-family: monospace; font-size: 12px; max-height: 100%; overflow-y: auto; padding-right: 10px; min-height: 100px;">
    <div class="compact-content">
        <?= $time_content ?>
        <?= $debug_log_stream_content ?>
    </div>
    <div class="expanded-content">
        <?= $time_content ?>
        <?= $debug_log_stream_content ?>
        <?= $session_content ?>
        <?= $post_content ?>
    </div>
</div>
