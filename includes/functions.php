<?php
/**
 * Global Utility Functions
 */

// BASE_URL is now defined in includes/config.php

/**
 * Sanitizes HTML output to prevent XSS.
 *
 * @param string $string The string to sanitize.
 * @return string The sanitized string.
 */
function sanitize_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Renders a view file.
 *
 * @param string $view_path The path to the view file (e.g., 'views/dashboard.php').
 * @param array $data An associative array of data to pass to the view.
 */
function render_view($view_path, array $data = []) {
    // Start output buffering to capture the view's content
    ob_start();

    // Ensure the view file exists before including it
    if (file_exists($view_path)) {
        // Make $data available to the view via a local variable
        // The view file can now access variables like $_data['key']
        $_data = $data; // Use a distinct variable name to avoid collision with view-level variables if any

        include $view_path;
    } else {
        debug_log("View file not found: " . $view_path, 'ERROR');
        // Optionally, render a 404 view or an error message
        echo "<p class=\"error\">Error: View could not be loaded.</p>";
    }

    // End output buffering and return the content
    ob_end_flush(); // Or ob_get_clean() if you want to return the content as a string
}

/**
 * Renders a card component.
 *
 * @param string $card_slug The slug of the card to render (e.g., 'printer_status_card').
 * @param array $data An associative array of data to pass to the card.
 */
function render_card($card_slug, array $data = []) {
    // Whitelist for card slugs to prevent arbitrary file inclusion for PoC
    $allowed_card_slugs = ['printer_status_card', 'toner_levels_card'];
    if (!in_array($card_slug, $allowed_card_slugs)) {
        debug_log("Attempted to render invalid card: " . $card_slug, 'WARNING');
        return; // Do not render if not whitelisted
    }

    $card_path = 'cards/' . $card_slug . '.php';
    render_view($card_path, $data);
}

/**
 * Simple logging function.
 *
 * @param string $message The message to log.
 * @param string $level The log level (INFO, WARNING, ERROR, DEBUG, SECURITY).
 */
function debug_log($message, $level = 'INFO') {
    // Configuration values for logging (from config.php)
    $log_levels = [
        'INFO' => LOG_INFO,
        'WARNING' => LOG_WARNING,
        'ERROR' => LOG_ERROR,
        'DEBUG' => LOG_DEBUG,
        'SECURITY' => LOG_SECURITY,
    ];

    // Check if logging is enabled for this level
    if (isset($log_levels[$level]) && $log_levels[$level] === true) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        // Log to debug panel (in-browser) if enabled
        if (DEBUG_PANEL_ENABLED) {
            if (!isset($_SESSION['debug_log']) || !is_array($_SESSION['debug_log'])) {
                $_SESSION['debug_log'] = [];
            }
            $_SESSION['debug_log'][] = $log_entry;
            // Trim log to avoid excessive memory usage in session
            while (count($_SESSION['debug_log']) > 500) { // Keep last 500 entries
                array_shift($_SESSION['debug_log']);
            }
        }

        // Log to file if enabled
        if (DEBUG_LOG_TO_FILE) {
            $log_file = LOG_FILE_PATH; // Use the constant for log file path
            // Check log file size and rotate if necessary
            if (file_exists($log_file) && filesize($log_file) > (MAX_DEBUG_LOG_SIZE_MB * 1024 * 1024)) {
                rename($log_file, $log_file . '.' . date('YmdHis') . '.old');
            }
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        }
    }
}

/**
 * Renders the debug panel HTML.
 */
function render_debug_panel() {
    echo '<div id="debug-panel" class="debug-panel hidden">';
    echo '<div class="debug-header">';
    echo '<h3>Debug Log <button id="debug-toggle" class="debug-button">Toggle</button></h3>';
    echo '</div>';
    echo '<div class="debug-content">';
    echo '<pre class="debug-log-output">';
    if (isset($_SESSION['debug_log']) && is_array($_SESSION['debug_log'])) {
        foreach ($_SESSION['debug_log'] as $entry) {
            echo sanitize_html($entry);
        }
    } else {
        echo 'No debug messages yet.';
    }
    echo '</pre>';
    echo '</div>';
    echo '</div>';
}