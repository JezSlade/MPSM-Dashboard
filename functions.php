<?php
// MPSM Dashboard - functions.php

// Global array to store debug log entries
// Ensure this is accessible. For simplicity, we declare it global.
// In a more complex app, this might be part of a logging class.
$debug_log_entries = [];

/**
 * Logs a message with a specific level (INFO, WARNING, ERROR, CRITICAL, DEBUG, SECURITY).
 * Messages are stored in a global array and optionally written to a file.
 *
 * @param string $message The message to log.
 * @param string $level The severity level (e.g., 'INFO', 'ERROR'). Case-insensitive.
 */
function debug_log($message, $level = 'INFO') {
    global $debug_log_entries; // Access the global log array

    // Normalize level to uppercase
    $level = strtoupper($level);

    // Access DEBUG_LOG_LEVELS constant from config.php
    $logLevels = defined('DEBUG_LOG_LEVELS') ? DEBUG_LOG_LEVELS : [];

    // Only log if DEBUG_MODE is enabled AND the specific level is enabled in config,
    // OR if it's a critical error (ERROR, CRITICAL) or SECURITY message (always log these).
    if (
        (defined('DEBUG_MODE') && DEBUG_MODE === true && isset($logLevels[$level]) && $logLevels[$level] === true) ||
        in_array($level, ['ERROR', 'CRITICAL', 'SECURITY'])
    ) {
        $log_entry = [
            'time' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
        ];
        $debug_log_entries[] = $log_entry;

        // Log to file if configured in config.php
        if (defined('DEBUG_LOG_TO_FILE') && DEBUG_LOG_TO_FILE && defined('DEBUG_LOG_FILE')) {
            $logFilePath = DEBUG_LOG_FILE;
            $logDirectory = dirname($logFilePath);

            // Ensure log directory exists BEFORE attempting to write
            if (!is_dir($logDirectory)) {
                // mkdir needs 0755 permissions and true for recursive creation
                if (!mkdir($logDirectory, 0755, true)) {
                    // Fallback to PHP error log if we can't create the custom log directory
                    error_log("Failed to create log directory: {$logDirectory}. Logging to PHP error log.");
                    return; // Abort file logging attempt
                }
            }

            // Optional: Basic log rotation/truncation
            if (defined('MAX_DEBUG_LOG_SIZE_MB') && MAX_DEBUG_LOG_SIZE_MB > 0) {
                if (file_exists($logFilePath) && filesize($logFilePath) / (1024 * 1024) > MAX_DEBUG_LOG_SIZE_MB) {
                    // Truncate the file (or implement full rotation if needed)
                    file_put_contents($logFilePath, "--- Log truncated due to size limit (" . MAX_DEBUG_LOG_SIZE_MB . " MB) ---\n", 0); // 0 means overwrite
                }
            }
            // Append the log entry to the file
            file_put_contents($logFilePath, "[{$log_entry['time']}] [{$log_entry['level']}] {$log_entry['message']}\n", FILE_APPEND);
        }

        // Optionally, also log to PHP's built-in error log for high-severity issues
        if (in_array($level, ['ERROR', 'CRITICAL', 'SECURITY'])) {
            error_log("[MPSM_APP_LOG][{$log_entry['level']}] {$message}");
        }
    }
}

/**
 * Includes a partial PHP file and passes data to it.
 *
 * @param string $filename The name of the partial file (e.g., 'header.php', 'views/dashboard.php').
 * It's expected to be relative to APP_BASE_PATH.
 * @param array $data An associative array of variables to extract into the partial's scope.
 * @return bool True if the partial was included successfully, false otherwise.
 */
function include_partial($filename, $data = []) {
    // Determine the full path to the partial.
    // Assuming APP_BASE_PATH is defined as the root of your application.
    // For partials like header.php, footer.php which are in the root, this works.
    // For other partials (e.g., in 'views/', 'cards/'), pass the full path from config (e.g., VIEWS_PATH . 'dashboard.php').
    $path = APP_BASE_PATH . $filename;

    if (file_exists($path)) {
        // Extract data into the current symbol table, making them available in the included file.
        // EXTR_SKIP prevents overwriting existing variables if a name collision occurs.
        extract($data, EXTR_SKIP);
        include $path;
        debug_log("Successfully included partial: " . $filename, 'DEBUG');
        return true;
    } else {
        debug_log("WARNING: Partial file not found: " . $path, 'WARNING');
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            // Display a user-friendly warning only in debug mode
            echo "<div class='warning-banner'>WARNING: Partial file '{$filename}' missing. Please check file paths.</div>";
        }
        return false;
    }
}

/**
 * Sanitizes a string for safe HTML output.
 *
 * @param string $string The string to sanitize.
 * @return string The sanitized string.
 */
function sanitize_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizes a string for use in URLs (e.g., as a slug).
 *
 * @param string $string The string to sanitize.
 * @return string The sanitized URL-friendly string.
 */
function sanitize_url($string) {
    // Remove all characters that are not letters, numbers, or hyphens/underscores
    $string = preg_replace('/[^a-zA-Z0-9_-]/', '', $string);
    // Replace multiple hyphens/underscores with a single one (optional)
    $string = preg_replace('/[-_]+/', '-', $string); // Replace multiple with a single hyphen
    // Trim hyphens/underscores from start/end
    $string = trim($string, '-_');
    // Convert to lowercase for consistency
    return strtolower($string);
}