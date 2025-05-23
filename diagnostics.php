<?php
// diagnostic.php — Resilient Debug Dashboard

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

define('BASE', __DIR__);
define('CORE', BASE . '/core');
define('LOG_FILE', BASE . '/logs/debug.log');

$results = [];
function add($cat,$check,$status,$details=''){
    global $results;
    $results[] = compact('cat','check','status','details');
}

// 1) Live Debug Log
$rawLog = is_readable(LOG_FILE)
    ? file_get_contents(LOG_FILE)
    : "— no debug entries yet —";

// 2) Environment
add('Environment','PHP ≥ 7.4', version_compare(PHP_VERSION,'7.4.0','>=')?'PASS':'FAIL',PHP_VERSION);
foreach(['pdo_mysql','curl','json'] as $ext){
    add('Environment',"Extension “{$ext}”", extension_loaded($ext)?'PASS':'FAIL');
}

// 3) .env presence
add('Config','.env file', is_readable(BASE.'/.env')?'PASS':'FAIL');

// 4) Conditional includes
$loaded = [];
$coreFiles = ['config.php','debug.php','api.php'];
foreach($coreFiles as $file){
    $path = CORE.'/'.$file;
    if(is_file($path)){
        require_once $path;
        $loaded[$file] = true;
        add('Code',"Loaded core/{$file}",'PASS');
    } else {
        $loaded[$file] = false;
        add('Code',"core/{$file} missing",'FAIL');
    }
}

// 5) File-structure from JSON (optional)
$jsonList = CORE.'/files.json';
if(is_readable($jsonList)){
    $spec = json_decode(file_get_contents($jsonList),true);
    $expected = $spec['core']??[];
    foreach($expected as $f){
        add('Filesystem',"core/{$f}", is_file(CORE.'/'.$f)?'PASS':'FAIL');
    }
}

// 6) Database test (only if config loaded)
if(!empty($loaded['config.php'])){
    try {
        $pdo = get_db();
        add('Database','Connect to DB','PASS');
    } catch(Exception $e){
        add('Database','Connect to DB','FAIL',$e->getMessage());
    }
} else {
    add('Database','Skipped DB test','INFO','config.php not loaded');
}

// 7) API smoke-tests (only if api loaded)
if(!empty($loaded['api.php']) && class_exists('ApiClient')){
    $api = new ApiClient();
    $eps = method_exists($api,'get_all_endpoints') ? $api->get_all_endpoints() : [];
    add('API','Endpoints defined','INFO',count($eps).' total');
    foreach(array_slice(array_keys($eps),0,3) as $ep){
        try {
            $res = $api->call_api($ep,'get',[]);
            $st  = is_array($res)?'PASS':'FAIL';
            $dt  = is_array($res)?'Keys:'.implode(',',array_slice(array_keys($res),0,3)):'Bad response';
        } catch(Exception $e){
            $st='FAIL'; $dt=$e->getMessage();
        }
        add('API',"GET {$ep}",$st,$dt);
    }
} else {
    add('API','Skipped API tests','INFO','api.php not loaded or ApiClient missing');
}

// Render dashboard
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Debug Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:sans-serif;margin:1em;}
    h1{margin-bottom:.5em;}
    pre.log{background:#222;color:#0f0;padding:1em;overflow:auto;max-height:200px;}
    table{width:100%;border-collapse:collapse;margin-top:1em;}
    th,td{border:1px solid #ccc;padding:.5em;text-align:left;}
    th{background:#eee;}
    .PASS{background:#dfd;}
    .FAIL{background:#fdd;}
    .INFO{background:#ddf;}
    .controls a{margin-right:1em;text-decoration:none;color:#06c;}
  </style>
</head>
<body>
  <h1>🔍 Debug Dashboard</h1>
  <div class="controls">
    <a href="diagnostic.php">↻ Refresh</a>
    <a href="javascript:location.reload(true)">🚿 Hard Reload</a>
  </div>

  <h2>Live Debug Log</h2>
  <pre class="log"><?= htmlspecialchars($rawLog) ?></pre>

  <h2>Health Checks</h2>
  <table>
    <tr><th>Category</th><th>Check</th><th>Status</th><th>Details</th></tr>
    <?php foreach($results as $r): ?>
    <tr class="<?= htmlspecialchars($r['status']) ?>">
      <td><?= htmlspecialchars($r['cat']) ?></td>
      <td><?= htmlspecialchars($r['check']) ?></td>
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td><?= htmlspecialchars($r['details']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
