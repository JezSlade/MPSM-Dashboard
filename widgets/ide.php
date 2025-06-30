<?php
// widgets/ide.php

/**
 * IDE Widget Configuration: $_widget_config
 * This defines the metadata for the IDE widget.
 */
$_widget_config = [
    'name' => 'File Editor (IDE)', // Display name
    'icon' => 'code',               // Font Awesome icon for code
    'width' => 4,                   // Default size: wide for an IDE
    'height' => 3                   // Default size: tall for an IDE
];


/**
 * Widget Rendering Function: render_ide_widget()
 * This function generates the HTML content for the IDE widget.
 * It provides both a compact overview and a detailed expanded view.
 *
 * IMPORTANT:
 * - This function definition MUST be wrapped in `if (!function_exists('...'))`.
 * - The function should ECHO or PRINT its HTML content.
 */
if (!function_exists('render_ide_widget')) {
    function render_ide_widget() {
        // Compact View Content: Simple overview
        echo '<div class="compact-content">';
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<p style="font-size: 28px; font-weight: bold; color: var(--accent); margin-bottom: 10px;">';
        echo '<i class="fas fa-code"></i> IDE';
        echo '</p>';
        echo '<p style="font-size: 14px; color: var(--text-secondary);">';
        echo 'Your web-based file editor.';
        echo '</p>';
        echo '</div>';
        echo '</div>'; // End .compact-content

        // Expanded View Content: The full IDE interface
        echo '<div class="expanded-content ide-container">';
        echo '<div class="ide-sidebar">';
        echo '    <div class="ide-path" id="ide-current-path">/</div>';
        echo '    <ul class="ide-file-tree" id="ide-file-tree">';
        echo '        <!-- File and directory listings will be loaded here via JavaScript -->';
        echo '        <li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>';
        echo '    </ul>';
        echo '</div>'; // End .ide-sidebar

        echo '<div class="ide-editor-area">';
        echo '    <div class="ide-editor-header">';
        echo '        <span id="ide-current-file-name">No file selected</span>';
        echo '        <span id="ide-file-status" class="ide-status-saved">Saved</span>';
        echo '        <button class="btn btn-primary" id="ide-save-btn" disabled><i class="fas fa-save"></i> Save</button>';
        echo '    </div>';
        echo '    <textarea id="ide-code-editor" class="ide-code-editor" placeholder="Select a file from the left to edit."></textarea>';
        echo '</div>'; // End .ide-editor-area

        echo '</div>'; // End .expanded-content
    }
}

/**
 * Return Function Name:
 * This line is crucial! It tells helpers.php which function within this file
 * should be called to render the widget's content.
 * Always return the string name of your rendering function.
 */
return 'render_ide_widget';
