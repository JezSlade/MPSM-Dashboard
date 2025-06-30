<?php
// widgets/ide.php

$_widget_config = [
    'name' => 'File Editor (IDE)',
    'icon' => 'code',
    'width' => 3,
    'height' => 3
];
?>
<div class="compact-content">
    <div style="text-align: center; padding: 20px;">
        <p style="font-size: 28px; font-weight: bold; color: var(--accent); margin-bottom: 10px;">
            <i class="fas fa-code"></i> IDE
        </p>
        <p style="font-size: 14px; color: var(--text-secondary);">
            Your web-based file editor.
        </p>
    </div>
</div>
<div class="expanded-content ide-container">
    <div class="ide-sidebar">
        <div class="ide-path" id="ide-current-path">/</div>
        <ul class="ide-file-tree" id="ide-file-tree">
            <li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>
        </ul>
    </div>
    <div class="ide-editor-area">
        <div class="ide-editor-header">
            <span id="ide-current-file-name">No file selected</span>
            <span id="ide-file-status" class="ide-status-saved">Saved</span>
            <button class="btn btn-primary" id="ide-save-btn" disabled><i class="fas fa-save"></i> Save</button>
        </div>
        <textarea id="ide-code-editor" class="ide-code-editor" placeholder="Select a file from the left to edit."></textarea>
    </div>
</div>
