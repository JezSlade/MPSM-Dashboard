<?php
// helpers.php

/**
 * Renders the content of a widget.
 * This function now includes the widget file directly.
 *
 * @param string $widget_id The ID of the widget to render.
 * @return string The HTML content of the widget, or an error message.
 */
function render_widget($widget_id) {
    $widget_file = APP_ROOT . '/widgets/' . basename($widget_id) . '.php'; // basename for security

    if (file_exists($widget_file)) {
        // Start output buffering to capture the widget's output
        ob_start();
        // Include the widget file. It's expected to define $_widget_config and output HTML.
        include $widget_file;
        $content = ob_get_clean(); // Get the buffered content and clean the buffer
        return $content;
    } else {
        return '<div style="text-align: center; padding: 20px; color: var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i> Unknown Widget: ' . htmlspecialchars($widget_id) .
               '</div>';
    }
}

?>
