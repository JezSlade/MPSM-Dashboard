<?php
// ide.php - Standalone IDE for editing widget source code

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define APP_ROOT if it's not already defined (e.g., if this file is accessed directly)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Get the file path from the URL parameter
$initial_file_path = $_GET['file'] ?? '';
$initial_file_name = basename($initial_file_path); // Extract just the file name for display

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDE - <?= htmlspecialchars($initial_file_name ?: 'File Editor') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css"> <!-- Reuse dashboard styles -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* Prevent body scroll, IDE will handle its own scroll */
            background-color: var(--bg-primary); /* Use dashboard background */
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }

        .ide-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: var(--bg-secondary); /* Use secondary background for header */
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            flex-shrink: 0; /* Prevent header from shrinking */
        }

        .ide-header h1 {
            margin: 0;
            font-size: 1.5em;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .ide-header h1 i {
            margin-right: 10px;
            color: var(--accent);
        }

        .ide-file-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .ide-file-path {
            font-size: 0.9em;
            color: var(--text-secondary);
        }

        #ide-current-file-name {
            font-weight: bold;
            color: var(--primary);
        }

        #ide-file-status {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 8px;
            background-color: rgba(0, 188, 212, 0.2);
            color: var(--primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .ide-status-saved {
            background-color: rgba(76, 175, 80, 0.2); /* Greenish for saved */
            color: var(--success);
        }

        .ide-status-unsaved {
            background-color: rgba(255, 193, 7, 0.2); /* Yellowish for unsaved */
            color: var(--warning);
        }

        .ide-content-wrapper {
            display: flex;
            flex-grow: 1; /* Take up remaining vertical space */
            overflow: hidden; /* Manage internal scrolling */
        }

        .ide-file-tree-panel {
            width: 280px; /* Fixed width for file tree */
            background-color: var(--bg-primary);
            border-right: 1px solid var(--glass-border);
            overflow-y: auto; /* Scrollable file tree */
            padding: 15px 0;
            flex-shrink: 0; /* Prevent shrinking */
        }

        .ide-file-tree {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ide-file-tree-item {
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            color: var(--text-primary);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .ide-file-tree-item:hover {
            background-color: rgba(0, 188, 212, 0.1);
            color: var(--accent);
        }

        .ide-file-tree-item.active {
            background-color: rgba(0, 188, 212, 0.2);
            color: var(--primary);
            font-weight: bold;
        }

        .ide-file-tree-item i {
            margin-right: 10px;
            font-size: 1.1em;
        }

        .ide-item-read-only {
            opacity: 0.6;
            cursor: not-allowed;
            color: var(--text-secondary);
        }

        .ide-item-read-only i {
            color: var(--text-secondary);
        }

        .ide-editor-panel {
            flex-grow: 1; /* Take up remaining horizontal space */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Manage internal scrolling */
            background-color: #1e1e1e; /* Dark background for editor */
        }

        .ide-code-editor {
            width: 100%;
            height: 100%;
            border: none;
            background-color: #1e1e1e; /* Dark background */
            color: #d4d4d4; /* Light text */
            padding: 20px;
            font-family: 'Fira Code', 'Cascadia Code', 'Consolas', monospace; /* Monospace font for code */
            font-size: 1em;
            resize: none; /* Disable textarea resize handle */
            outline: none;
            line-height: 1.5;
            tab-size: 4;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            overflow: auto; /* Enable scrolling for editor content */
        }

        .ide-actions-footer {
            padding: 10px 20px;
            background-color: var(--bg-secondary);
            border-top: 1px solid var(--glass-border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0; /* Prevent footer from shrinking */
        }

        .ide-loading-indicator, .ide-error-indicator {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
        }

        .ide-error-indicator {
            color: var(--danger);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="ide-header">
        <h1><i class="fas fa-code"></i> Widget IDE</h1>
        <div class="ide-file-info">
            <span class="ide-file-path">Current File: <span id="ide-current-file-name"><?= htmlspecialchars($initial_file_path) ?></span></span>
            <span id="ide-file-status" class="ide-status-saved">Saved</span>
        </div>
    </div>

    <div class="ide-content-wrapper">
        <div class="ide-file-tree-panel">
            <ul class="ide-file-tree" id="ide-file-tree">
                <div class="ide-loading-indicator">
                    <i class="fas fa-spinner fa-spin"></i> Loading files...
                </div>
            </ul>
        </div>

        <div class="ide-editor-panel">
            <textarea id="ide-code-editor" class="ide-code-editor" spellcheck="false"></textarea>
            <div class="ide-actions-footer">
                <button class="btn btn-primary" id="ide-save-button">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>

    <script type="module">
        // Pass initial file path to JavaScript
        window.initialFilePath = "<?= htmlspecialchars($initial_file_path) ?>";
    </script>
    <script type="module" src="src/js/features/IdeWidget.js"></script>
</body>
</html>
