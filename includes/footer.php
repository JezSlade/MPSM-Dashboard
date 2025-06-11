<?php
/**
 * includes/footer.php
 *
 * Renders:
 * - Footer
 * - Fixed overlay debug panel
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

<?php if (DEBUG_MODE && DEBUG_PANEL_ENABLED): debug_log("Rendering debug panel", 'DEBUG'); ?>
  <div id="debug-panel" class="debug-panel">
    <div class="debug-header">
      <h3>Debug Log</h3>
      <button id="debug-toggle-visibility" class="debug-button">_</button>
      <button id="debug-clear-log" class="debug-button">X</button>
    </div>
    <div class="debug-content">
      <pre id="debug-log-output" class="debug-log-output">
<?php
    if (empty($GLOBALS['debug_messages'])) {
        echo "[INFO] No log messages yet.\n";
    } else {
        foreach($GLOBALS['debug_messages'] as $line) {
            echo sanitize_html($line) . "\n";
        }
    }
?>
      </pre>
    </div>
  </div>
  <script>
    document.getElementById('debug-toggle-visibility').addEventListener('click',()=>{
      const p=document.getElementById('debug-panel');
      p.style.display = p.style.display==='none'?'flex':'flex';
    });
    document.getElementById('debug-clear-log').addEventListener('click',()=>{
      document.getElementById('debug-log-output').textContent="[INFO] Log cleared.";
    });
  </script>
<?php debug_log("Footer complete", 'DEBUG'); endif; ?>
