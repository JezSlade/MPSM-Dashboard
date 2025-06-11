<?php
/**
 * includes/footer.php
 *
 * Dashboard footer and debug panel.
 */
if(!isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
    debug_log("Initialized debug_messages", 'DEBUG');
}
debug_log("Rendering footer", 'DEBUG');
?>
<footer class="dashboard-footer">
  <p>&copy; <?php echo date('Y'); ?> <?php echo sanitize_html(APP_NAME); ?>. Version <?php echo sanitize_html(APP_VERSION); ?>.</p>
</footer>

<?php if (DEBUG_MODE && DEBUG_PANEL_ENABLED): ?>
  <div id="debug-panel" class="debug-panel">
    <div class="debug-header">
      <h3>Debug Log</h3>
      <button id="debug-toggle-visibility" class="debug-button" title="Toggle Panel">−</button>
      <button id="debug-clear-log" class="debug-button" title="Clear Logs">🗑️</button>
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
    const p = document.getElementById('debug-panel');
    const t = document.getElementById('debug-toggle-visibility');
    const c = document.getElementById('debug-clear-log');
    const o = document.getElementById('debug-log-output');
    if (localStorage.getItem('debugPanelHidden') === 'true') {
      p.classList.add('hidden');
    }
    t.addEventListener('click', () => {
      p.classList.toggle('hidden');
      localStorage.setItem('debugPanelHidden', p.classList.contains('hidden'));
    });
    c.addEventListener('click', () => { o.textContent = "[INFO] Logs cleared.\n"; });
  })();
  </script>
<?php endif; ?>
