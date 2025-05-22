<?php
// diagnostic.php — Enhanced Debug Dashboard v1.1.0

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');

// No cache
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

define('DIAG_VERSION', '1.1.0');
define('BASE_PATH', __DIR__);

// Bootstrap core functions (get_db, debug_log, fetch_mps_api, etc.)
require_once BASE_PATH . '/core/bootstrap.php';
require_once BASE_PATH . '/core/api.php';

// Collect results
$results = [];

// Helper
function add($cat, $name, $status, $msg = '') {
    global $results;
    $results[] = ['cat'=>$cat,'name'=>$name,'status'=>$status,'msg'=>$msg];
}

// 1. Environment checks
add('Environment','PHP version ≥ 7.4', version_compare(PHP_VERSION,'7.4.0','>=') ? 'PASS' : 'FAIL', PHP_VERSION);
foreach (['pdo_mysql','curl','json'] as $ext) {
    add('Environment',"Extension “{$ext}”", extension_loaded($ext) ? 'PASS' : 'FAIL');
}

// 2. Config (.env)
$envPath = BASE_PATH . '/.env';
if (file_exists($envPath) && is_readable($envPath)) {
    add('Config','.env file readable','PASS');
} else {
    add('Config','.env missing or unreadable','FAIL');
}

// 3. Core files
$core = ['core/config.php','core/debug.php','core/api.php','core/widgets.php'];
foreach ($core as $f) {
    $path = BASE_PATH.'/'.$f;
    add('Code',"File {$f}", is_readable($path)?'PASS':'FAIL');
}

// 4. Filesystem
foreach (['logs','cache'] as $d) {
    $dir = BASE_PATH.'/'.$d;
    add('FS',"Directory {$d}/ writable", is_writable($dir)?'PASS':'FAIL');
}

// 5. Database
$env = parse_ini_file($envPath, false, INI_SCANNER_RAW) ?: [];
if (!empty($env['DB_HOST']) && !empty($env['DB_NAME'])) {
    try {
        $pdo = get_db();
        add('Database',"Connect to {$env['DB_NAME']}", 'PASS');
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        add('Database','Tables count','INFO', count($tables).' tables');
    } catch (Exception $e) {
        add('Database','Connection','FAIL',$e->getMessage());
    }
} else {
    add('Database','DB credentials','FAIL','Missing in .env');
}

// 6. API endpoint definitions
$api = new ApiClient();
$endpoints = $api->get_all_endpoints();
add('API','Endpoints defined','INFO', count($endpoints).' endpoints');

// 7. API calls
foreach ($endpoints as $path => $ops) {
    foreach (['get','post','put','delete'] as $method) {
        if (isset($ops[$method])) {
            $id = $ops[$method]['operationId'] ?? strtoupper($method).' '.$path;
            try {
                $resp = $api->call_api($path, $method, []);
                $stat = is_array($resp) ? 'PASS' : 'FAIL';
                $msg  = is_array($resp) ? ('Keys: '.implode(',', array_slice(array_keys($resp),0,3))) : 'No array';
            } catch (Exception $e) {
                $stat = 'FAIL';
                $msg  = $e->getMessage();
            }
            add('API Test',"$method $path",$stat,$msg);
        }
    }
}

// Render
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><title>Debug Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:sans-serif;margin:1em}
    h1{margin-bottom:.5em}
    table{width:100%;border-collapse:collapse;font-size:.9em}
    th,td{border:1px solid #ddd;padding:.4em;text-align:left}
    th{background:#f0f0f0}
    .PASS{background:#dfd}
    .FAIL{background:#fdd}
    .INFO{background:#ddf}
    .controls a{margin-right:1em}
  </style>
</head>
<body>
  <h1>🔍 Debug Dashboard v<?= DIAG_VERSION ?></h1>
  <div class="controls">
    <a href="diagnostic.php">Refresh</a>
    <a href="?clear_cache=1" onclick="localStorage.clear();sessionStorage.clear();alert('Cleared');return false;">Clear Browser Cache & Storage</a>
  </div>
  <table>
    <tr><th>Category</th><th>Check</th><th>Status</th><th>Details</th></tr>
  <?php foreach($results as $r): ?>
    <tr class="<?= htmlspecialchars($r['status']) ?>">
      <td><?= htmlspecialchars($r['cat']) ?></td>
      <td><?= htmlspecialchars($r['name']) ?></td>
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td><?= htmlspecialchars($r['msg']) ?></td>
    </tr>
  <?php endforeach; ?>
  </table>
</body>
</html>
