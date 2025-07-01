<?php
// widgets/ide.php

// Widget configuration
$_widget_config = [
    'name' => 'File Editor (IDE)',
    'icon' => 'code',
    'width' => 3,
    'height' => 3
];
?>
<div class="compact-content">
    <div style="text-align: center; padding: 20px;">
        <p style="font-size: 20px; font-weight: bold; color: var(--accent);">
            <i class="fas fa-code"></i> File Editor
        </p>
        <p style="font-size: 14px; color: var(--text-secondary);">
            Expand to use the integrated code editor.
        </p>
    </div>
</div>
<div class="expanded-content ide-container">
    <div class="ide-header">
        <div class="ide-path-display" id="ide-current-path">/</div>
        <div class="ide-file-info">
            <span id="ide-current-file-name">No file selected</span>
            <span id="ide-file-status" class="ide-status-saved"></span>
        </div>
        <button class="btn btn-sm btn-primary" id="ide-save-btn" disabled>
            <i class="fas fa-save"></i> Save
        </button>
    </div>
    <div class="ide-content">
        <div class="ide-file-tree-panel">
            <ul id="ide-file-tree" class="ide-file-tree">
                <li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>
            </ul>
        </div>
        <div class="ide-editor-panel">
            <textarea id="ide-code-editor" class="ide-code-editor" spellcheck="false" readonly></textarea>
        </div>
    </div>
</div>
