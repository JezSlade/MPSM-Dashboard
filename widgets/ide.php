<?php
// widgets/ide.php

// Widget Name: File Editor (IDE)
// Widget Icon: fas fa-code
// Widget Width: 3.0
// Widget Height: 3.0
?>
<div class="compact-content">
    <div class="neomorphic-card p-4 text-center h-full flex flex-col justify-center items-center">
        <p class="text-2xl font-bold text-[var(--accent)] mb-2">
            <i class="fas fa-code text-4xl mb-2"></i> File Editor
        </p>
        <p class="text-sm text-[var(--text-secondary)]">
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
        <button class="btn btn-sm btn-primary ripple-effect" id="ide-save-btn" disabled>
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
