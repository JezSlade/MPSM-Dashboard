<?php
/**
 * MPSM Dashboard - Footer Partial
 *
 * This file contains the HTML structure and PHP logic for the dashboard's footer section.
 * Crucially, it also houses the dynamic debug panel, which displays all logged messages.
 *
 * Debugging Philosophy:
 * The debug panel itself should be resilient. Even if other parts of the application fail,
 * it should attempt to load and display information about those failures.
 * Its own loading process is also logged.
 */

// Ensure debug_messages global array is initialized.
if (!isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
    debug_log("DEBUG: Initialized \$GLOBALS['debug_messages'] in footer.php (was empty).", 'DEBUG');
}

debug_log("Rendering footer.php and debug panel.", 'INFO');
?>
<footer class="dashboard-footer">
    <p>&copy; <?php echo date('Y'); ?> <?php echo sanitize_html(APP_NAME); ?>. Version <?php echo sanitize_html(APP_VERSION); ?>.</p>
</footer>

<?php
// --- Debug Panel ---
// The debug panel is only rendered if DEBUG_MODE and DEBUG_PANEL_ENABLED are true.
if (defined('DEBUG_MODE') && DEBUG_MODE && defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
    debug_log("DEBUG_PANEL_ENABLED is true. Attempting to render debug panel.", 'INFO');
?>
    <div id="debug-panel" class="debug-panel glassmorphic">
        <div class="debug-header">
            <h3>Debug Log</h3>
            <button id="debug-toggle-visibility" class="debug-button" title="Toggle Debug Panel Visibility">_</button>
            <button id="debug-clear-log" class="debug-button" title="Clear Log">X</button>
        </div>
        <div class="debug-content">
            <pre id="debug-log-output" class="debug-log-output">
<?php
                // --- Debug Panel Self-Check and Loading Messages ---
                // This section specifically helps diagnose if the debug panel itself fails to load.
                if (!isset($GLOBALS['debug_messages'])) {
                    echo "[CRITICAL DEBUG ERROR] \$GLOBALS['debug_messages'] array is not defined. Debug logging will not function.\n";
                } else if (empty($GLOBALS['debug_messages'])) {
                    echo "[INFO] Debug panel loaded, but no messages logged yet.\n";
                } else {
                    echo "[INFO] Debug panel loaded and displaying " . count($GLOBALS['debug_messages']) . " log entries.\n";
                }
                echo "[INFO] PHP Version: " . PHP_VERSION . "\n";
                echo "[INFO] Current Time: " . date('Y-m-d H:i:s') . "\n";
                echo "[INFO] APP_BASE_PATH: " . (defined('APP_BASE_PATH') ? APP_BASE_PATH : 'NOT DEFINED') . "\n";
                echo "[INFO] DEBUG_LOG_FILE: " . (defined('DEBUG_LOG_FILE') ? DEBUG_LOG_FILE : 'NOT DEFINED') . "\n";

                // Output all collected debug messages.
                foreach ($GLOBALS['debug_messages'] as $log_entry) {
                    // Sanitize messages to prevent XSS in the debug panel itself.
                    echo sanitize_html($log_entry) . "\n";
                }
?>
            </pre>
        </div>
    </div>
    <?php debug_log("Debug panel successfully rendered.", 'INFO'); ?>
<?php
} else {
    debug_log("DEBUG_MODE or DEBUG_PANEL_ENABLED is false. Debug panel is not rendered.", 'INFO');
}
?>

<?php debug_log("Footer.php rendering complete.", 'INFO'); ?>