<?php
// diagnostic.php — Self-contained Debug Dashboard

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');

// Always output HTML head immediately
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Debug Dashboard</title>
  <style>
    body{font-family:sans-serif;margin:1em}
    h1{margin-bottom:.5em}
    pre.log{background:#222;color:#0f0;padding:1em;overflow:auto;max-height:200px;}
    table{width:100%;border-collapse:collapse;margin-top:1em;font-size:.9em}
    th,td{border:1px solid #ccc;padding:.5em;text-align:left}
    th{background:#eee}
    .PASS{background:#dfd}
    .FAIL{background:#fdd}
    .INFO{background:#ddf}
    .controls a{margin-right:1em;color:#06c;text-decoration:none}
  </style>
</head>
<body>
  <h1>🔍 Debug Dashboard</h1>
  <div class="controls">
    <a href="diagnostic.php">↻ Refresh</a>
    <a href="javascript:location.reload(true)">🚿 Hard Reload</a>
  </div>

<?php
// Collect results
$results = [];
function add($cat,$check,$status,$details='') {
    global $results;
    $results[] = ['cat'=>$cat,'check'=>$check,'status'=>$status,'details'=>$details];
}

// 1) Live Debug Log
$logFile = __DIR__ . '/logs/debug.log';
$rawLog = is_readable($logFile)
    ? file_get_contents($logFile)
    : "— no debug entries yet —";
echo "<h2>Live Debug Log</h2><pre class=\"log\">".htmlspecialchars($rawLog)."</pre>";

// 2) Environment
add('Environment','PHP ≥ 7.4', version_compare(PHP_VERSION,'7.4.0','>=')?'PASS':'FAIL', PHP_VERSION);
foreach (['pdo_mysql','curl','json'] as $ext) {
    add('Environment',"Extension “{$ext}”", extension_loaded($ext)?'PASS':'FAIL');
}

// 3) .env presence
add('Config','.env file', is_readable(__DIR__.'/.env')?'PASS':'FAIL');

// 4) Core directory files
$coreDir = __DIR__ . '/core';
$expected = ['config.php','db.php','debug.php','api.php','widgets.php','files.json'];
foreach ($expected as $f) {
    $path = $coreDir . '/' . $f;
    add('Filesystem',"core/{$f}", is_file($path)?'PASS':'FAIL');
}

// 5) Database connectivity
// Only if we can parse .env
$env = [];
if (is_readable(__DIR__.'/.env')) {
    foreach (file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line=trim($line);
        if (!$line||$line[0]==='#') continue;
        [$k,$v]=explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}
if (!empty($env['DB_HOST']) && !empty($env['DB_NAME'])) {
    try {
        $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USER'] ?? '', $env['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        add('Database','DB connect','PASS');
    } catch (Throwable $e) {
        add('Database','DB connect','FAIL',$e->getMessage());
    }
} else {
    add('Database','DB credentials','FAIL','Missing DB_HOST/DB_NAME in .env');
}

// 6) API smoke-test (simple cURL to token URL)
if (!empty($env['TOKEN_URL'])) {
    $ch = curl_init($env['TOKEN_URL']);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY    => true,
        CURLOPT_TIMEOUT   => 5,
        CURLOPT_FAILONERROR => true,
    ]);
    $ok = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($ok) add('API','Reach TOKEN_URL','PASS');
    else    add('API','Reach TOKEN_URL','FAIL',$err);
} else {
    add('API','TOKEN_URL','INFO','Not set in .env');
}

// Render health-check table
echo "<h2>Health Checks</h2><table>";
echo "<tr><th>Category</th><th>Check</th><th>Status</th><th>Details</th></tr>";
foreach ($results as $r) {
    echo "<tr class=\"{$r['status']}\">"
       ."<td>".htmlspecialchars($r['cat'])."</td>"
       ."<td>".htmlspecialchars($r['check'])."</td>"
       ."<td>".htmlspecialchars($r['status'])."</td>"
       ."<td>".htmlspecialchars($r['details'])."</td>"
       ."</tr>";
}
echo "</table>";
?>

</body>
</html>
