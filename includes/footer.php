<?php
/**
 * includes/footer.php
 *
 * Dashboard Footer Partial
 *
 * Renders:
 * - Debug panel (if enabled)
 * - Closing HTML tags
 */
?>
        </main> <?php
        // Render debug panel if enabled
        if (defined('DEBUG_PANEL_ENABLED') && DEBUG_PANEL_ENABLED) {
            render_debug_panel();
        }
        ?>
    </div><footer class="dashboard-footer">
      <p>&copy; 2025 <?php echo sanitize_html(APP_NAME); ?>. Version <?php echo sanitize_html(APP_VERSION); ?>.</p>
    </footer>
</body>
</html>