<?php
/**
 * includes/footer.php
 *
 * MPSM Dashboard – Footer Partial
 * Contains:
 *  - Copyright/version
 *  - Fixed overlay debug panel showing all debug_messages
 */

if (!isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
    debug_log("Initialized \$GLOBALS['debug_messages'] in footer.php", 'DEBUG');
}

debug_log("Rendering footer.php and debug panel.", 'INFO');
?>
<footer class="dashboard-footer">
    <p>&copy; <?php echo date('Y'); ?> <?php echo sanitize_html(APP_NAME); ?>. Version <?php echo sanitize_html(APP_VERSION); ?>.</p>
</footer>

<?php
if (defined('DEBUG_MODE') && DEBUG_MODE && defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
    debug_log("DEBUG_PANEL_ENABLED is true. Rendering debug panel.", 'INFO');
?>
    <div id="debug-panel" class="debug-panel">
      <div class="debug-header">
        <h3>Debug Log</h3>
        <button id="debug-toggle-visibility" class="debug-button" title="Toggle Visibility">_</button>
        <button id="debug-clear-log" class="debug-button" title="Clear Log">X</button>
      </div>
      <div class="debug-content">
        <pre id="debug-log-output" class="debug-log-output">
<?php
    if (empty($GLOBALS['debug_messages'])) {
        echo "[INFO] Debug panel loaded, but no messages logged yet.\n";
    } else {
        foreach ($GLOBALS['debug_messages'] as $line) {
            echo sanitize_html($line) . "\n";
        }
    }
?>
        </pre>
      </div>
    </div>
    <script>
      document.getElementById('debug-toggle-visibility').addEventListener('click', () => {
        const panel = document.getElementById('debug-panel');
        panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
      });
      document.getElementById('debug-clear-log').addEventListener('click', () => {
        document.getElementById('debug-log-output').textContent = "[INFO] Log cleared.";
      });
    </script>
<?php
} else {
    debug_log("Debug panel not rendered (DEBUG_MODE or DEBUG_PANEL_ENABLED false).", 'INFO');
}
debug_log("Footer.php rendering complete.", 'INFO');
?>
