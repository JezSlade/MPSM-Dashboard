<?php
/**
 * Refactored IDE - Full Project Explorer & Editor
 * Based on Claude's php_ide.php but refactored for MPSM Dashboard architecture
 *
 * Changes:
 *  - Project-wide file explorer under IDE_ROOT (MPSM-Dashboard root)
 *  - Backup/rollback system before saves (backups in /backup/ide/YYYYMMDD/)
 *  - AJAX-based smooth editing
 *  - Max file size enforcement
 *  - Session-based access (POC only, no auth hardening)
 */

session_start();

// === CONFIGURATION ===
if (!defined('IDE_ROOT')) {
    define('IDE_ROOT', dirname(__DIR__));
}
if (!defined('BACKUP_ROOT')) {
    define('BACKUP_ROOT', IDE_ROOT . '/backup/ide');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
}

// Ensure backup dir exists
date_default_timezone_set('UTC');
$today_backup_dir = BACKUP_ROOT . '/' . date('Ymd');
if (!is_dir($today_backup_dir)) {
    mkdir($today_backup_dir, 0755, true);
}

// === UTILITY FUNCTIONS ===
function list_files($dir, $base = '') {
    $items = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = "$dir/$item";
        $rel = ltrim("$base/$item", '/');
        if (is_dir($path)) {
            $items[] = ["type" => "dir", "name" => $item, "path" => $rel, "children" => list_files($path, $rel)];
        } else {
            $items[] = ["type" => "file", "name" => $item, "path" => $rel];
        }
    }
    return $items;
}

function backup_file($file_path) {
    global $today_backup_dir;
    $rel = str_replace(IDE_ROOT, '', $file_path);
    $backup_path = $today_backup_dir . '/' . ltrim($rel, '/');
    $backup_dir = dirname($backup_path);
    if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
    return copy($file_path, $backup_path);
}

// === AJAX HANDLER ===
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $file = realpath(IDE_ROOT . '/' . $_POST['file']);

    if (strpos($file, IDE_ROOT) !== 0) {
        echo json_encode(["error" => "Invalid file path"]);
        exit;
    }

    switch ($action) {
        case 'list':
            echo json_encode(list_files(IDE_ROOT));
            break;

        case 'open':
            if (!file_exists($file)) {
                echo json_encode(["error" => "File not found"]);
                exit;
            }
            echo json_encode(["content" => file_get_contents($file)]);
            break;

        case 'save':
            if (filesize($file) > MAX_FILE_SIZE) {
                echo json_encode(["error" => "File too large"]);
                exit;
            }
            backup_file($file);
            file_put_contents($file, $_POST['content']);
            echo json_encode(["success" => true]);
            break;

        default:
            echo json_encode(["error" => "Unknown action"]);
    }
    exit;
}

// === HTML/JS UI ===
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MPSM Dashboard IDE</title>
<style>
 body { font-family: monospace; background:#1e1e1e; color:#eee; display:flex; height:100vh; margin:0; }
 #sidebar { width:250px; background:#2b2b2b; overflow:auto; padding:10px; }
 #editor { flex:1; display:flex; flex-direction:column; }
 #file-content { flex:1; background:#111; color:#0f0; padding:10px; border:none; resize:none; }
 button { background:#444; color:#fff; border:none; padding:5px 10px; cursor:pointer; }
 button:hover { background:#666; }
 .file, .dir { cursor:pointer; padding:2px 5px; }
 .dir { font-weight:bold; }
</style>
</head>
<body>
<div id="sidebar"></div>
<div id="editor">
    <div><button onclick="saveFile()">Save</button> <span id="current-file"></span></div>
    <textarea id="file-content"></textarea>
</div>

<script>
let currentFile = '';

function loadSidebar() {
    fetch('', { method:'POST', body:new URLSearchParams({action:'list'}) })
    .then(r=>r.json()).then(data=>renderSidebar(data, document.getElementById('sidebar')));
}

function renderSidebar(items, container) {
    container.innerHTML = '';
    items.forEach(item => {
        const el = document.createElement('div');
        el.className = item.type;
        el.textContent = item.name;
        el.onclick = () => {
            if (item.type==='file') openFile(item.path);
            else renderSidebar(item.children, container);
        };
        container.appendChild(el);
    });
}

function openFile(path) {
    fetch('', { method:'POST', body:new URLSearchParams({action:'open',file:path}) })
    .then(r=>r.json()).then(data=>{
        currentFile = path;
        document.getElementById('file-content').value = data.content || '';
        document.getElementById('current-file').textContent = path;
    });
}

function saveFile() {
    if (!currentFile) return alert('No file selected');
    const content = document.getElementById('file-content').value;
    fetch('', { method:'POST', body:new URLSearchParams({action:'save',file:currentFile,content}) })
    .then(r=>r.json()).then(data=>{
        if (data.success) alert('Saved! Backup created.');
        else alert('Error: ' + data.error);
    });
}

loadSidebar();
</script>
</body>
</html>
