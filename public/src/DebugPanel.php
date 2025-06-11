<?php
/**
 * DebugPanel.php
 *
 * Provides a simple, on-page debug log.
 * It only renders when DEBUG_MODE is defined and true.
 */

class DebugPanel
{
    /**
     * Log a debug message and optional data.
     *
     * @param string $message  A brief description of the event.
     * @param mixed  $data     Optional; additional context (array, object, etc.)
     */
    public static function log(string $message, $data = null): void
    {
        // If our debug flag isn't set or is false, do nothing.
        if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
            return;
        }

        // Protect against contexts without a proper request (e.g., CLI).
        if (php_sapi_name() === 'cli') {
            return;
        }

        // Timestamp for readability
        $timestamp = date('Y-m-d H:i:s');

        // Render the debug entry
        echo '<div class="debug-log" style="font-family:monospace; background:#333; color:#eee; padding:8px; margin:4px 0;">';
        echo '<strong>[' . htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8') . ']</strong> ';
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        if ($data !== null) {
            echo '<pre style="white-space:pre-wrap; margin-top:4px; color:#0f0;">'
                . htmlspecialchars(print_r($data, true), ENT_QUOTES, 'UTF-8')
                . '</pre>';
        }

        echo '</div>';
    }
}
