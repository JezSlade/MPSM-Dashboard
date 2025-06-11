<?php
/**
 * includes/footer.php
 *
 * Renders:
 *  - Dashboard footer
 *  - Fixed overlay debug panel (persistent state, modern buttons, scrollable)
 */

if (!isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
    debug_log("Initialized debug_messages", 'DEBUG');
}
debug_log("Rendering footer", 'DEBUG');
?>
<footer class="dashboard-footer">
  <p>&copy; <?php echo date('Y'); ?> <?php echo sanitize_html(APP_NAME); ?>. Version <?php echo sanitize_html(APP_VERSION); ?>.</p>
</footer>

<?php if (defined('DEBUG_MODE') && DEBUG_MODE && defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED): ?>
  <div id="debug-panel" class="debug-panel">
    <div class="debug-header">
      <h3>Debug Log</h3>
      <button id="debug-toggle-visibility" class="debug-button" title="Toggle Panel">−</button>
      <button id="debug-clear-log"      class="debug-button" title="Clear Logs">🗑️</button>
    </div>
    <div class="debug-content">
      <pre id="debug-log-output" class="debug-log-output">
<?php
      if (empty($GLOBALS['debug_messages'])) {
          echo "[INFO] No log messages yet.\n";
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
  (function(){
    const panel  = document.getElementById('debug-panel');
    const toggle = document.getElementById('debug-toggle-visibility');
    const clear  = document.getElementById('debug-clear-log');
    const output = document.getElementById('debug-log-output');

    // Restore last open/closed state
    if (localStorage.getItem('debugPanelHidden') === 'true') {
      panel.classList.add('hidden');
    }

    // Toggle panel visibility
    toggle.addEventListener('click', () => {
      panel.classList.toggle('hidden');
      localStorage.setItem('debugPanelHidden', panel.classList.contains('hidden'));
    });

    // Clear the log output
    clear.addEventListener('click', () => {
      output.textContent = "[INFO] Logs cleared.\n";
    });
  })();
  </script>
<?php
  debug_log("Footer and debug panel rendered", 'DEBUG');
endif;
