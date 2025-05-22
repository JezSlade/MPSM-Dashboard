<?php
// diagnostic.php — Comprehensive debug dashboard
// VERSION 1.0.0 (increment this on each change)
define('DIAG_VERSION', '1.0.0');
define('BASE_PATH', __DIR__);

// Collect all messages
$results = [];

// Helper to add a result
function add_result($category, $name, $status, $details = '') {
    global $results;
    $results[] = compact('category','name','status','details');
}

// 1. PHP environment
add_result('Environment','PHP Version', version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'WARN', PHP_VERSION);
foreach (['pdo','curl','json','session'] as $ext) {
    add_result('Environment', "Extension “{$ext}” loaded", extension_loaded($ext) ? 'OK' : 'FAIL');
}

// 2. .env parsing
$env_file = BASE_PATH.'/.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file, false, INI_SCANNER_RAW);
    if ($env !== false) {
        add_result('Config', '.env parsed', 'OK', count($env).' variables');
    } else {
        add_result('Config', '.env parse error', 'FAIL');
    }
} else {
    add_result('Config', '.env file missing', 'FAIL');
}

// 3. Config class exists?
add_result('Code','Config class exists', class_exists('\Core\Config') ? 'OK' : 'FAIL');

// 4. Logger class exists
add_result('Code','Logger class exists', class_exists('\Core\Logger') ? 'OK' : 'FAIL');

// 5. Core files presence & readability
$core_files = [
    '/core/bootstrap.php',
    '/core/config.php',
    '/core/auth.php',
    '/core/api.php',
    '/core/widgets.php',
    '/core/debug.php',
];
foreach ($core_files as $f) {
    $path = BASE_PATH . $f;
    $status = is_readable($path) ? 'OK' : 'FAIL';
    add_result('Code', "{$f} readable", $status, $status==='OK' ? '' : 'Check file exists and permissions');
}

// 6. Writable directories
$wdirs = [
    '/logs',
    '/cache',
    '/config',
];
foreach ($wdirs as $d) {
    $full = BASE_PATH . $d;
    $status = is_writable($full) ? 'OK' : 'FAIL';
    add_result('Filesystem', "{$d} writable", $status);
}

// 7. Database connection
if (isset($env['DB_HOST'],$env['DB_NAME'],$env['DB_USER'])) {
    try {
        $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        add_result('Database','Connect to '.$env['DB_NAME'], 'OK');
        // list tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        add_result('Database','Tables found', 'INFO', count($tables).' tables: '.implode(', ', array_slice($tables,0,5)).(count($tables)>5?'…':''));
    } catch (Exception $e) {
        add_result('Database','Connection failed','FAIL',$e->getMessage());
    }
} else {
    add_result('Database','DB credentials in .env','FAIL','DB_HOST/DB_NAME/DB_USER missing');
}

// 8. Session test
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    add_result('Runtime','Session start','OK','ID='.session_id());
} else {
    add_result('Runtime','Session start','FAIL');
}

// 9. CURL test
$ch = curl_init('https://api.abassetmanagement.com');
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
add_result('Network','Outbound HTTPS','OK',$code?'HTTP '.$code:'No response');

// 10. JSON endpoints file
$ep = BASE_PATH.'/config/endpoints.json';
if (file_exists($ep)) {
    $cnt = count(json_decode(file_get_contents($ep), true)['paths'] ?? []);
    add_result('API','Endpoints JSON loaded','OK',$cnt.' endpoints');
} else {
    add_result('API','Endpoints JSON missing','FAIL');
}

// Render HTML
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🔧 Diagnostic Dashboard</title>
    <style>
        body { font-family:sans-serif; margin:1em; }
        table { border-collapse: collapse; width:100%; }
        th,td { border:1px solid #ccc; padding:0.5em; text-align:left; }
        th { background:#eee; }
        .OK { background:#cfc; }
        .WARN { background:#ffeb99; }
        .FAIL { background:#f99; }
        .INFO { background:#ccf; }
        .controls { margin-bottom:1em; }
        .controls a { margin-right:1em; }
    </style>
</head>
<body>
    <h1>🔧 Diagnostic Dashboard</h1>
    <div class="controls">
        <strong>Version:</strong> <?php echo DIAG_VERSION;?> |
        <a href="?clear_cache=1">🧹 Clear Browser Cache</a>
        <a href="diagnostic.php">↻ Refresh</a>
    </div>
    <?php if(isset($_GET['clear_cache'])): ?>
        <p><em>To fully clear cache, please hard-reload (Ctrl+F5 / ⌘+⇧+R).</em></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Category</th><th>Check</th><th>Status</th><th>Details</th></tr>
        </thead>
        <tbody>
        <?php foreach($results as $r): ?>
            <tr class="<?php echo $r['status'];?>"
                title="<?php echo htmlspecialchars($r['details']);?>">
                <td><?php echo htmlspecialchars($r['category']);?></td>
                <td><?php echo htmlspecialchars($r['name']);?></td>
                <td><?php echo htmlspecialchars($r['status']);?></td>
                <td><?php echo htmlspecialchars($r['details']);?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>

    <h2>Quick “Fix Permissions”</h2>
    <p>If any <code>Filesystem</code> checks failed, run on your server (adjust user/group):</p>
    <pre>chown -R www-data:www-data <?php echo htmlspecialchars(BASE_PATH);?>/logs
chmod -R 775 <?php echo htmlspecialchars(BASE_PATH);?>/logs
chown -R www-data:www-data <?php echo htmlspecialchars(BASE_PATH);?>/cache
chmod -R 775 <?php echo htmlspecialchars(BASE_PATH);?>/cache</pre>

    <p><em>This dashboard must remain in your project root for one-click diagnostics. Remove or restrict access in production.</em></p>
</body>
</html>
