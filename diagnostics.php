<?php
// diagnostic.php — Comprehensive Debug Dashboard v1.1.1

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1'); 

// no cache
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

define('DIAG_VERSION','1.1.1');
define('BASE', __DIR__);
define('CORE_DIR', BASE.'/core');

// collect results
$results = [];
function add($cat,$name,$status,$msg='') {
    global $results;
    $results[] = compact('cat','name','status','msg');
}

// 1. Environment
add('Env','PHP ≥ 7.4', version_compare(PHP_VERSION,'7.4.0','>=')?'PASS':'FAIL',PHP_VERSION);
foreach(['pdo_mysql','curl','json'] as $e) {
    add('Env',"Ext {$e}",extension_loaded($e)?'PASS':'FAIL');
}

// 2. Config file
add('Config','.env',is_readable(BASE.'/.env')?'PASS':'FAIL');

// 3. Core structure
// load expected list from JSON if exists
$files_json = CORE_DIR.'/files.json';
if (is_readable($files_json)) {
    $spec = json_decode(file_get_contents($files_json),true);
    $expected = $spec['core'] ?? [];
} else {
    // fallback
    $expected = [
      'bootstrap.php','config.php','auth.php',
      'api.php','widgets.php','debug.php',
      'db.php','Logger.php'
    ];
}
foreach ($expected as $f) {
    $path = CORE_DIR.'/'.$f;
    add('Filesystem',"core/{$f}",is_file($path)?'PASS':'FAIL');
}

// 4. File inventory (sample)
$all_core = array_map('basename', glob(CORE_DIR.'/*.php'));
add('Filesystem','core/*.php count', 'INFO', count($all_core).' files');

// 5. Database
if (is_readable(BASE.'/.env')) {
    $env = parse_ini_file(BASE.'/.env',false,INI_SCANNER_RAW) ?: [];
    if (!empty($env['DB_NAME'])) {
        try {
            require CORE_DIR.'/config.php';
            $pdo = get_db();
            add('DB',"Connect {$env['DB_NAME']}",'PASS');
        } catch(Exception $e){
            add('DB','Connect','FAIL',$e->getMessage());
        }
    } else {
        add('DB','DB_NAME in .env','FAIL','Missing');
    }
} else {
    add('DB','.env missing','FAIL');
}

// render
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Debug Dashboard v<?=DIAG_VERSION?></title>
<style>
  body{font-family:sans-serif;margin:1em}table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #ccc;padding:.4em;font-size:.9em}
  th{background:#eee} .PASS{background:#dfd} .FAIL{background:#fdd} .INFO{background:#ddf}
</style>
</head><body>
  <h1>🔍 Debug Dashboard v<?=DIAG_VERSION?></h1>
  <table>
    <tr><th>Category</th><th>Check</th><th>Status</th><th>Details</th></tr>
  <?php foreach($results as $r): ?>
    <tr class="<?=htmlspecialchars($r['status'])?>">
      <td><?=htmlspecialchars($r['cat'])?></td>
      <td><?=htmlspecialchars($r['name'])?></td>
      <td><?=htmlspecialchars($r['status'])?></td>
      <td><?=htmlspecialchars($r['msg'])?></td>
    </tr>
  <?php endforeach; ?>
  </table>
</body>
</html>
