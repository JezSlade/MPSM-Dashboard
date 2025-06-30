<?php
$_widget_config = [
    'name' => 'Recent Activity',
    'icon' => 'history',
    'width' => 2,
    'height' => 1
];

// Simple PHP IDE - Single File
// Place this file in your project directory and access via web browser

// Only start session if one isn't already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuration
$base_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : __DIR__;
if (!$base_dir || !is_dir($base_dir)) {
    $base_dir = __DIR__;
}
$current_dir = isset($_GET['current_dir']) ? $_GET['current_dir'] : '';
$allowed_extensions = ['php', 'txt', 'html', 'css', 'js', 'json', 'md'];

// Security: Prevent directory traversal
if (!function_exists('sanitize_path')) {
    function sanitize_path($path) {
        global $base_dir;
        // Handle both absolute and relative paths
        if (is_absolute_path($path)) {
            $real_path = realpath($path);
        } else {
            $real_path = realpath($base_dir . '/' . $path);
        }
        
        if ($real_path === false || strpos($real_path, realpath($base_dir)) !== 0) {
            return false;
        }
        return $real_path;
    }
}

if (!function_exists('is_absolute_path')) {
    function is_absolute_path($path) {
        return (substr($path, 0, 1) === '/' || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[a-zA-Z]:/', $path)));
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'load_file':
            $file_path = sanitize_path($_POST['file']);
            if ($file_path && is_file($file_path)) {
                echo json_encode([
                    'success' => true,
                    'content' => file_get_contents($file_path),
                    'file' => $_POST['file']
                ]);
            } else {
                // Debug info
                echo json_encode([
                    'success' => false, 
                    'error' => 'File not found',
                    'debug' => [
                        'requested_file' => $_POST['file'],
                        'sanitized_path' => $file_path,
                        'base_dir' => $GLOBALS['base_dir'],
                        'file_exists' => $file_path ? file_exists($file_path) : false,
                        'is_file' => $file_path ? is_file($file_path) : false
                    ]
                ]);
            }
        case 'change_directory':
            $new_dir = sanitize_path($_POST['directory']);
            if ($new_dir && is_dir($new_dir)) {
                echo json_encode([
                    'success' => true,
                    'directory' => str_replace(realpath($GLOBALS['base_dir']), '', $new_dir)
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Directory not found']);
            }
            exit;
            
        case 'save_file':
            $file_path = sanitize_path($_POST['file']);
            if ($file_path) {
                $result = file_put_contents($file_path, $_POST['content']);
                echo json_encode([
                    'success' => $result !== false,
                    'error' => $result === false ? 'Failed to save file' : null
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid file path']);
            }
            exit;
            
        case 'create_file':
            $file_path = sanitize_path($_POST['file']);
            $dir = dirname($file_path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if ($file_path && !file_exists($file_path)) {
                $result = file_put_contents($file_path, $_POST['content'] ?? '');
                echo json_encode([
                    'success' => $result !== false,
                    'error' => $result === false ? 'Failed to create file' : null
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'File already exists or invalid path']);
            }
            exit;
            
        case 'delete_file':
            $file_path = sanitize_path($_POST['file']);
            if ($file_path && is_file($file_path)) {
                $result = unlink($file_path);
                echo json_encode([
                    'success' => $result,
                    'error' => !$result ? 'Failed to delete file' : null
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'File not found']);
            }
            exit;
            
        case 'create_folder':
            $folder_path = sanitize_path($_POST['folder']);
            if ($folder_path && !is_dir($folder_path)) {
                $result = mkdir($folder_path, 0755, true);
                echo json_encode([
                    'success' => $result,
                    'error' => !$result ? 'Failed to create folder' : null
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Folder already exists or invalid path']);
            }
            exit;
    }
}

// Get directory listing
if (!function_exists('get_directory_tree')) {
    function get_directory_tree($dir, $prefix = '') {
        global $allowed_extensions, $base_dir;
        
        // Fallback if global variable is not set
        if (!is_array($allowed_extensions)) {
            $allowed_extensions = ['php', 'txt', 'html', 'css', 'js', 'json', 'md'];
        }
        
        $items = [];
        
        if (!is_dir($dir)) return $items;
        
        $files = scandir($dir);
        sort($files);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $full_path = $dir . '/' . $file;
            $relative_path = $prefix . $file;
            
            if (is_dir($full_path)) {
                $items[] = [
                    'type' => 'folder',
                    'name' => $file,
                    'path' => $relative_path,
                    'full_path' => $full_path,
                    'children' => get_directory_tree($full_path, $relative_path . '/')
                ];
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_extensions)) {
                    $items[] = [
                        'type' => 'file',
                        'name' => $file,
                        'path' => $relative_path,
                        'full_path' => $full_path,
                        'extension' => $ext
                    ];
                }
            }
        }
        
        return $items;
    }
}

$directory_tree = get_directory_tree($base_dir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP IDE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            height: 100vh;
            display: flex;
        }
        
        .sidebar {
            width: 300px;
            background: #252526;
            border-right: 1px solid #3e3e42;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 10px;
            background: #2d2d30;
            border-bottom: 1px solid #3e3e42;
            font-weight: bold;
        }
        
        .file-tree {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        
        .tree-item {
            padding: 4px 0;
            cursor: pointer;
            user-select: none;
        }
        
        .tree-item:hover {
            background: #2a2d2e;
        }
        
        .tree-item.selected {
            background: #094771;
        }
        
        .folder {
            font-weight: bold;
            color: #75beff;
        }
        
        .file {
            color: #d4d4d4;
            margin-left: 20px;
        }
        
        .file.php { color: #8993f7; }
        .file.html { color: #e34c26; }
        .file.css { color: #1572b6; }
        .file.js { color: #f7df1e; }
        .file.json { color: #85ea2d; }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .toolbar {
            padding: 10px;
            background: #2d2d30;
            border-bottom: 1px solid #3e3e42;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .toolbar button {
            background: #0e639c;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .toolbar button:hover {
            background: #1177bb;
        }
        
        .toolbar input {
            background: #3c3c3c;
            border: 1px solid #5a5a5a;
            color: #d4d4d4;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .editor-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .file-tabs {
            background: #2d2d30;
            border-bottom: 1px solid #3e3e42;
            display: flex;
            overflow-x: auto;
        }
        
        .file-tab {
            padding: 8px 16px;
            background: #3c3c3c;
            border-right: 1px solid #3e3e42;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-tab.active {
            background: #1e1e1e;
        }
        
        .file-tab .close {
            color: #999;
            font-weight: bold;
            margin-left: 8px;
        }
        
        .file-tab .close:hover {
            color: #fff;
        }
        
        .editor {
            flex: 1;
            background: #1e1e1e;
        }
        
        .editor textarea {
            width: 100%;
            height: 100%;
            background: #1e1e1e;
            color: #d4d4d4;
            border: none;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
            resize: none;
            outline: none;
        }
        
        .status-bar {
            padding: 5px 15px;
            background: #007acc;
            color: white;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }
        
        .welcome-message {
            padding: 40px;
            text-align: center;
            color: #888;
        }
        
        .context-menu {
            position: absolute;
            background: #2d2d30;
            border: 1px solid #5a5a5a;
            border-radius: 3px;
            padding: 5px 0;
            min-width: 150px;
            z-index: 1000;
            display: none;
        }
        
        .context-menu-item {
            padding: 8px 15px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .context-menu-item:hover {
            background: #094771;
        }
        
        .children {
            margin-left: 15px;
        }
        
        .folder-icon::before {
            content: "📁 ";
        }
        
        .file-icon::before {
            content: "📄 ";
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            File Explorer
            <div style="font-size: 11px; color: #888; margin-top: 5px;">
                <input type="text" id="currentPath" value="<?php echo htmlspecialchars($base_dir); ?>" style="width: 100%; background: #3c3c3c; border: 1px solid #5a5a5a; color: #d4d4d4; padding: 2px 4px; font-size: 10px;">
                <button onclick="changeDirectory()" style="margin-top: 3px; font-size: 10px; padding: 2px 6px;">Go</button>
                <button onclick="goUpDirectory()" style="font-size: 10px; padding: 2px 6px;">Up</button>
            </div>
        </div>
        <div class="file-tree" id="fileTree">
            <?php
            if (!function_exists('render_tree_item')) {
                function render_tree_item($item, $level = 0) {
                    $indent = str_repeat('  ', $level);
                    if ($item['type'] === 'folder') {
                        echo "<div class='tree-item folder' data-path='{$item['path']}' style='padding-left: " . ($level * 15 + 4) . "px'>";
                        echo "<span class='folder-icon'></span>{$item['name']}";
                        echo "</div>";
                        if (!empty($item['children'])) {
                            echo "<div class='children'>";
                            foreach ($item['children'] as $child) {
                                render_tree_item($child, $level + 1);
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='tree-item file {$item['extension']}' data-path='{$item['path']}' style='padding-left: " . ($level * 15 + 4) . "px'>";
                        echo "<span class='file-icon'></span>{$item['name']}";
                        echo "</div>";
                    }
                }
            }
            
            foreach ($directory_tree as $item) {
                render_tree_item($item);
            }
            ?>
        </div>
    </div>
    
    <div class="main-content">
        <div class="toolbar">
            <button onclick="newFile()">New File</button>
            <button onclick="newFolder()">New Folder</button>
            <button onclick="saveFile()" id="saveBtn" disabled>Save</button>
            <button onclick="deleteFile()" id="deleteBtn" disabled>Delete</button>
            <input type="text" id="searchBox" placeholder="Search files..." style="margin-left: auto; width: 200px;">
        </div>
        
        <div class="file-tabs" id="fileTabs"></div>
        
        <div class="editor-container">
            <div class="editor" id="editor">
                <div class="welcome-message">
                    <h2>Welcome to Simple PHP IDE</h2>
                    <p>Select a file from the sidebar to start editing</p>
                </div>
            </div>
        </div>
        
        <div class="status-bar">
            <span id="statusLeft">Ready</span>
            <span id="statusRight"></span>
        </div>
    </div>
    
    <div class="context-menu" id="contextMenu">
        <div class="context-menu-item" onclick="contextNewFile()">New File</div>
        <div class="context-menu-item" onclick="contextNewFolder()">New Folder</div>
        <div class="context-menu-item" onclick="contextDelete()">Delete</div>
    </div>

    <script>
        let openTabs = {};
        let activeTab = null;
        let contextTarget = null;
        
        // File tree interaction
        document.getElementById('fileTree').addEventListener('click', function(e) {
            const item = e.target.closest('.tree-item');
            if (!item) return;
            
            // Remove previous selection
            document.querySelectorAll('.tree-item.selected').forEach(el => {
                el.classList.remove('selected');
            });
            
            item.classList.add('selected');
            
            if (item.classList.contains('file')) {
                openFile(item.dataset.path);
            } else if (item.classList.contains('folder')) {
                // Double-click to navigate into folder
                if (item.dataset.lastClick && (Date.now() - item.dataset.lastClick) < 300) {
                    navigateToFolder(item.dataset.path);
                }
                item.dataset.lastClick = Date.now();
            }
        });
        
        // Context menu
        document.getElementById('fileTree').addEventListener('contextmenu', function(e) {
            e.preventDefault();
            const item = e.target.closest('.tree-item');
            if (!item) return;
            
            contextTarget = item;
            const menu = document.getElementById('contextMenu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        });
        
        document.addEventListener('click', function() {
            document.getElementById('contextMenu').style.display = 'none';
        });
        
        function openFile(path) {
            if (openTabs[path]) {
                switchTab(path);
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=load_file&file=' + encodeURIComponent(path)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    openTabs[path] = {
                        content: data.content,
                        modified: false,
                        element: null
                    };
                    createTab(path);
                    switchTab(path);
                    updateStatus('File loaded: ' + path);
                } else {
                    console.error('Load file error:', data);
                    alert('Error loading file: ' + data.error + (data.debug ? '\nDebug: ' + JSON.stringify(data.debug, null, 2) : ''));
                }
            });
        }
        
        function navigateToFolder(path) {
            window.location.href = '?dir=' + encodeURIComponent(document.getElementById('currentPath').value + '/' + path);
        }
        
        function changeDirectory() {
            const newPath = document.getElementById('currentPath').value;
            window.location.href = '?dir=' + encodeURIComponent(newPath);
        }
        
        function goUpDirectory() {
            const currentPath = document.getElementById('currentPath').value;
            const parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
            if (parentPath) {
                window.location.href = '?dir=' + encodeURIComponent(parentPath);
            }
        }
        
        function createTab(path) {
            const tabsContainer = document.getElementById('fileTabs');
            const tab = document.createElement('div');
            tab.className = 'file-tab';
            tab.dataset.path = path;
            
            const fileName = path.split('/').pop();
            tab.innerHTML = `
                <span>${fileName}</span>
                <span class="close" onclick="closeTab('${path}', event)">×</span>
            `;
            
            tab.addEventListener('click', (e) => {
                if (!e.target.classList.contains('close')) {
                    switchTab(path);
                }
            });
            
            tabsContainer.appendChild(tab);
        }
        
        function switchTab(path) {
            // Update tab appearance
            document.querySelectorAll('.file-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`[data-path="${path}"]`).classList.add('active');
            
            // Create or switch editor
            const editor = document.getElementById('editor');
            if (!openTabs[path].element) {
                const textarea = document.createElement('textarea');
                textarea.value = openTabs[path].content;
                textarea.addEventListener('input', () => {
                    openTabs[path].modified = true;
                    openTabs[path].content = textarea.value;
                    updateTabTitle(path);
                    updateButtons();
                });
                openTabs[path].element = textarea;
            }
            
            editor.innerHTML = '';
            editor.appendChild(openTabs[path].element);
            activeTab = path;
            updateButtons();
            updateStatus('Editing: ' + path);
        }
        
        function closeTab(path, event) {
            event.stopPropagation();
            
            if (openTabs[path].modified) {
                if (!confirm('File has unsaved changes. Close anyway?')) {
                    return;
                }
            }
            
            document.querySelector(`[data-path="${path}"]`).remove();
            delete openTabs[path];
            
            if (activeTab === path) {
                const remainingTabs = Object.keys(openTabs);
                if (remainingTabs.length > 0) {
                    switchTab(remainingTabs[0]);
                } else {
                    document.getElementById('editor').innerHTML = `
                        <div class="welcome-message">
                            <h2>Welcome to Simple PHP IDE</h2>
                            <p>Select a file from the sidebar to start editing</p>
                        </div>
                    `;
                    activeTab = null;
                    updateButtons();
                }
            }
        }
        
        function updateTabTitle(path) {
            const tab = document.querySelector(`[data-path="${path}"]`);
            const fileName = path.split('/').pop();
            const modified = openTabs[path].modified ? ' •' : '';
            tab.querySelector('span').textContent = fileName + modified;
        }
        
        function updateButtons() {
            const saveBtn = document.getElementById('saveBtn');
            const deleteBtn = document.getElementById('deleteBtn');
            
            if (activeTab) {
                saveBtn.disabled = !openTabs[activeTab].modified;
                deleteBtn.disabled = false;
            } else {
                saveBtn.disabled = true;
                deleteBtn.disabled = true;
            }
        }
        
        function saveFile() {
            if (!activeTab) return;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=save_file&file=' + encodeURIComponent(activeTab) + '&content=' + encodeURIComponent(openTabs[activeTab].content)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    openTabs[activeTab].modified = false;
                    updateTabTitle(activeTab);
                    updateButtons();
                    updateStatus('File saved: ' + activeTab);
                } else {
                    alert('Error saving file: ' + data.error);
                }
            });
        }
        
        function newFile() {
            const fileName = prompt('Enter file name:');
            if (!fileName) return;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create_file&file=' + encodeURIComponent(fileName) + '&content='
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh to show new file
                } else {
                    alert('Error creating file: ' + data.error);
                }
            });
        }
        
        function newFolder() {
            const folderName = prompt('Enter folder name:');
            if (!folderName) return;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create_folder&folder=' + encodeURIComponent(folderName)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh to show new folder
                } else {
                    alert('Error creating folder: ' + data.error);
                }
            });
        }
        
        function deleteFile() {
            if (!activeTab) return;
            
            if (!confirm('Are you sure you want to delete this file?')) return;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_file&file=' + encodeURIComponent(activeTab)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeTab(activeTab, {stopPropagation: () => {}});
                    location.reload(); // Refresh file tree
                } else {
                    alert('Error deleting file: ' + data.error);
                }
            });
        }
        
        function contextNewFile() {
            const basePath = contextTarget.dataset.path;
            const isFolder = contextTarget.classList.contains('folder');
            const parentPath = isFolder ? basePath : basePath.substring(0, basePath.lastIndexOf('/'));
            
            const fileName = prompt('Enter file name:');
            if (!fileName) return;
            
            const fullPath = parentPath ? parentPath + '/' + fileName : fileName;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create_file&file=' + encodeURIComponent(fullPath) + '&content='
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error creating file: ' + data.error);
                }
            });
        }
        
        function contextNewFolder() {
            const basePath = contextTarget.dataset.path;
            const isFolder = contextTarget.classList.contains('folder');
            const parentPath = isFolder ? basePath : basePath.substring(0, basePath.lastIndexOf('/'));
            
            const folderName = prompt('Enter folder name:');
            if (!folderName) return;
            
            const fullPath = parentPath ? parentPath + '/' + folderName : folderName;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create_folder&folder=' + encodeURIComponent(fullPath)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error creating folder: ' + data.error);
                }
            });
        }
        
        function contextDelete() {
            if (!confirm('Are you sure you want to delete this item?')) return;
            
            const path = contextTarget.dataset.path;
            const action = contextTarget.classList.contains('folder') ? 'delete_folder' : 'delete_file';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: action + '=true&file=' + encodeURIComponent(path)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting item: ' + data.error);
                }
            });
        }
        
        function updateStatus(message) {
            document.getElementById('statusLeft').textContent = message;
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        saveFile();
                        break;
                    case 'n':
                        e.preventDefault();
                        newFile();
                        break;
                }
            }
        });
        
        // Search functionality
        document.getElementById('searchBox').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.tree-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = query ? 'none' : 'block';
                }
            });
        });
        
        updateStatus('Ready');
    </script>
</body>
</html>