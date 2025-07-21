<?php
/**
 * Refactored IDE - Widgets Directory Fix
 * Ensure IDE_ROOT points correctly to the widgets folder and handles empty dirs gracefully.
 */

session_start();

// === CONFIGURATION ===
if (!defined('IDE_ROOT')) {
    define('IDE_ROOT', __DIR__ . '/widgets'); // Ensure it uses this script's directory widgets folder
}
if (!is_dir(IDE_ROOT)) {
    mkdir(IDE_ROOT, 0755, true);
}
if (!defined('BACKUP_ROOT')) {
    define('BACKUP_ROOT', __DIR__ . '/backup/ide');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
}

date_default_timezone_set('UTC');
$today_backup_dir = BACKUP_ROOT . '/' . date('Ymd');
if (!is_dir($today_backup_dir)) mkdir($today_backup_dir, 0755, true);

function list_files($dir, $base = '') {
    if (!is_dir($dir)) return [];
    $items = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = "$dir/$item";
        $rel = ltrim("$base/$item", '/');
        if (is_dir($path)) {
            $items[] = ["type"=>"dir","name"=>$item,"path"=>$rel,"children"=>list_files($path,$rel)];
        } else {
            $size = filesize($path);
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            $items[] = ["type"=>"file","name"=>$item." (".($ext?:'unknown')." - ".number_format($size/1024,1)."KB)","path"=>$rel];
        }
    }
    return $items;
}

function backup_file($file_path) {
    global $today_backup_dir;
    $rel = str_replace(IDE_ROOT, '', $file_path);
    $backup_path = $today_backup_dir . '/' . ltrim($rel, '/');
    if (!is_dir(dirname($backup_path))) mkdir(dirname($backup_path), 0755, true);
    return copy($file_path, $backup_path);
}

if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $a=$_POST['action'];
    $f=realpath(IDE_ROOT.'/'.($_POST['file']??''));
    if ($f && strpos($f, IDE_ROOT)!==0){echo json_encode(["error"=>"Invalid file path"]);exit;}
    switch($a){
        case 'list':echo json_encode(list_files(IDE_ROOT));break;
        case 'open':echo json_encode(["content"=>file_exists($f)?file_get_contents($f):'']);break;
        case 'save':if(filesize($f)>MAX_FILE_SIZE){echo json_encode(["error"=>"File too large"]);exit;}backup_file($f);file_put_contents($f,$_POST['content']);echo json_encode(["success"=>true]);break;
        default:echo json_encode(["error"=>"Unknown action"]);
    }exit;
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><title>MPSM Dashboard IDE - Widgets</title>
<style>
 body{font-family:monospace;background:#1e1e1e;color:#eee;display:flex;height:100vh;margin:0}
 #sidebar{width:300px;background:#2b2b2b;overflow:auto;padding:10px}
 #search{width:100%;padding:3px;margin-bottom:5px}
 #editor{flex:1;display:flex;flex-direction:column}
 #file-content{flex:1;background:#111;color:#0f0;padding:10px;border:none;resize:none}
 .dir,.file{cursor:pointer;padding:3px 5px}
 .dir{font-weight:bold}
 .collapsed>.children{display:none}
 .breadcrumb{padding:5px;background:#333;margin-bottom:5px}
 .breadcrumb span{cursor:pointer;color:#6cf}
</style></head>
<body>
<div id="sidebar"><input id="search" placeholder="Search files..." oninput="filterFiles(this.value)"></div>
<div id="editor">
 <div class="breadcrumb" id="breadcrumb">Widgets Directory</div>
 <div><button onclick="saveFile()">Save</button> <span id="current-file"></span></div>
 <textarea id="file-content"></textarea>
</div>
<script>
let currentFile='',allItems=[];
function loadSidebar(){fetch('',{method:'POST',body:new URLSearchParams({action:'list'})}).then(r=>r.json()).then(d=>{allItems=d;renderSidebar(d,document.getElementById('sidebar'))})}
function renderSidebar(items,c){let s=document.getElementById('search');c.innerHTML='';if(s&&!c.contains(s))c.prepend(s);items.forEach(it=>{const el=document.createElement('div');el.className=it.type;el.textContent=it.name;if(it.type==='file')el.onclick=()=>openFile(it.path);else{el.onclick=()=>toggleDir(el,it);const ch=document.createElement('div');ch.className='children';el.appendChild(ch)}c.appendChild(el)})}
function filterFiles(q){if(!q){renderSidebar(allItems,document.getElementById('sidebar'));return;}q=q.toLowerCase();function filter(i){return i.filter(it=>it.type==='dir'? (it.children=filter(it.children)).length||it.name.toLowerCase().includes(q):it.name.toLowerCase().includes(q))}renderSidebar(filter(JSON.parse(JSON.stringify(allItems))),document.getElementById('sidebar'))}
function toggleDir(el,it){el.classList.toggle('collapsed');renderSidebar(it.children,el.querySelector('.children'))}
function openFile(p){fetch('',{method:'POST',body:new URLSearchParams({action:'open',file:p})}).then(r=>r.json()).then(d=>{currentFile=p;document.getElementById('file-content').value=d.content||'';document.getElementById('current-file').textContent=p;updateBreadcrumb(p)})}
function saveFile(){if(!currentFile)return alert('No file selected');fetch('',{method:'POST',body:new URLSearchParams({action:'save',file:currentFile,content:document.getElementById('file-content').value})}).then(r=>r.json()).then(d=>alert(d.success?'Saved! Backup created.':'Error: '+d.error))}
function updateBreadcrumb(p){const bc=document.getElementById('breadcrumb');bc.innerHTML='Widgets / ';const parts=p.split('/');let full='';parts.forEach((seg,i)=>{if(!seg)return;full+=(i?'/':'')+seg;const s=document.createElement('span');s.textContent=seg;s.onclick=()=>jumpToBreadcrumb(full);bc.appendChild(s);if(i<parts.length-1)bc.appendChild(document.createTextNode(' / '))})}
function jumpToBreadcrumb(path){alert('Future: focus on '+path)}
loadSidebar();
</script></body></html>
