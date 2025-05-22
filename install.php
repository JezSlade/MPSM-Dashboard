<?php
// install.php — Installer with Reset, Fix Permissions, Clear Browser Data

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');

// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$envPath   = __DIR__ . '/.env';
$debugFile = __DIR__ . '/logs/debug.log';

// Handle “Start Over”
if (isset($_GET['reset'])) {
    @unlink($envPath);
    @unlink($debugFile);
    session_destroy();
    header('Location: install.php');
    exit;
}

// Handle “Fix Permissions”
if (isset($_GET['fixperm'])) {
    $errors = [];
    // Attempt to set writable permissions on logs/
    if (!@chmod(__DIR__ . '/logs', 0777)) {
        $errors[] = 'Could not chmod logs/ to 0777';
    }
    // Optionally, ensure project root is writable
    if (!@chmod(__DIR__, 0755)) {
        $errors[] = 'Could not chmod project root to 0755';
    }
    // Log the fix attempt
    file_put_contents($debugFile, "FixPerm: " . (empty($errors) ? 'OK' : implode('; ', $errors)) . "\n", FILE_APPEND);
    header('Location: install.php');
    exit;
}

// Helpers
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fatal($msg){
    echo "<div style='color:red;padding:1rem;background:#fee;border:1px solid #f00;'>Fatal: ".h($msg)."</div>";
    exit;
}
function atomicWrite($path,$data){
    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $data) === false) return false;
    chmod($tmp, 0600);
    return rename($tmp, $path);
}

// Clear old debug log on fresh load
if (file_exists($debugFile) && !isset($_GET['step'])) {
    @unlink($debugFile);
}

// Determine step
$step = $_GET['step'] ?? '1';
if (!in_array($step, ['1','2','3'], true)) {
    $step = '1';
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer (Step <?= h($step) ?>)</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 1rem; }
    .card { max-width: 700px; margin: auto; background: #fff; padding: 1rem; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    .btn, .btn-link { display: inline-block; padding: .5rem 1rem; margin-top: 1rem; text-decoration: none; }
    .btn { background: #007bff; color: #fff; border: none; cursor: pointer; }
    .btn-link { background: #f8f9fa; color: #007bff; border: 1px solid #007bff; }
    input { width: 100%; padding: .5rem; margin: .5rem 0; }
    h2 { margin-top: 1rem; }
    pre { background: #eee; padding: 1rem; overflow: auto; }
    .topnav { text-align: right; margin-bottom: 1rem; }
    .topnav a { margin-left: 1rem; }
  </style>
  <script>
    function clearBrowserData(){
      try {
        localStorage.clear();
        sessionStorage.clear();
        document.cookie.split(";").forEach(function(c) {
          document.cookie = c.replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });
        alert("Browser data cleared. Please reload the page.");
      } catch(e){ alert("Failed to clear browser data: "+e); }
    }
  </script>
</head>
<body>
<div class="card">
  <div class="topnav">
    Step <?= h($step) ?> of 3
    <a class="btn-link" href="?reset=1">Start Over</a>
    <a class="btn-link" href="?fixperm=1">Fix Permissions</a>
    <a class="btn-link" href="javascript:clearBrowserData();">Clear Browser Data</a>
  </div>

  <details open>
    <summary>Debug Console</summary>
    <pre><?= file_exists($debugFile) ? h(file_get_contents($debugFile)) : '— no debug entries yet —' ?></pre>
  </details>

<?php if ($step === '1'): 
    // Prereq checks
    $checks = [
        'PHP ≥ 7.4'        => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO MySQL'        => extension_loaded('pdo_mysql'),
        'cURL'             => extension_loaded('curl'),
        'JSON'             => extension_loaded('json'),
        'Writable logs/'   => is_writable(__DIR__ . '/logs'),
        'Writable project' => is_writable(__DIR__),
    ];
    echo "<h2>Step 1: Configuration</h2><ul>";
    $allOk = true;
    foreach ($checks as $label => $ok) {
        echo "<li>" . ($ok ? '✅' : '❌') . " " . h($label) . "</li>";
        if (!$ok) $allOk = false;
    }
    echo "</ul>";
    if (!$allOk) {
        fatal('Please fix the above prerequisites and reload.');
    }
?>
  <form method="POST" action="?step=1">
    <h2>Database Configuration</h2>
    <input name="DB_HOST" placeholder="DB_HOST (e.g. localhost)" required>
    <input name="DB_NAME" placeholder="DB_NAME (e.g. resolut7_mpsm)" required>
    <input name="DB_USER" placeholder="DB_USER (e.g. resolut7_mpsm)" required>
    <input type="password" name="DB_PASS" placeholder="DB_PASS">

    <h2>API Configuration</h2>
    <input name="CLIENT_ID" placeholder="API Client ID" required>
    <input type="password" name="CLIENT_SECRET" placeholder="API Client Secret" required>
    <input name="API_USER" placeholder="API Username (optional)">
    <input type="password" name="API_PASS" placeholder="API Password (optional)">
    <input name="SCOPE" placeholder="API Scope (e.g. account)" required>
    <input name="TOKEN_URL" placeholder="API Token URL" required>
    <input name="BASE_URL" placeholder="API Base URL" required>

    <h2>Admin Account</h2>
    <input name="ADMIN_USER" placeholder="Admin Username">
    <input type="password" name="ADMIN_PASS" placeholder="Admin Password">

    <button class="btn">Save & Continue →</button>
  </form>
<?php
    exit;
endif;

// STEP 1 POST: write .env, create DB, bootstrap & seed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '1') {
    echo "<pre>";
    try {
        // Build .env
        $keys = ['DB_HOST','DB_NAME','DB_USER','DB_PASS',
                 'CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS',
                 'SCOPE','TOKEN_URL','BASE_URL',
                 'ADMIN_USER','ADMIN_PASS'];
        $lines = [];
        foreach ($keys as $k) {
            if (!isset($_POST[$k])) throw new Exception("$k is required");
            $v = str_replace(["\r","\n"], '', $_POST[$k]);
            $lines[] = "$k={$v}";
        }
        if (!atomicWrite($envPath, implode("\n", $lines) . "\n")) {
            throw new Exception("Failed to write .env");
        }
        echo "✅ .env written\n";

        // Create DB if missing
        $h = $_POST['DB_HOST'];
        $u = $_POST['DB_USER'];
        $p = $_POST['DB_PASS'];
        $d = $_POST['DB_NAME'];
        $pdoRoot = new PDO(
            "mysql:host={$h};charset=utf8mb4",
            $u, $p,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdoRoot->exec(
            "CREATE DATABASE IF NOT EXISTS `{$d}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        echo "✅ Database `{$d}` ready\n";

        // Bootstrap & seed admin
        require __DIR__ . '/core/bootstrap.php';
        echo "✅ Migrations & default admin seeded\n";

        // Seed widgets
        $spec = @json_decode(@file_get_contents(__DIR__ . '/fullApi.json'), true);
        if (!is_array($spec['paths'] ?? null)) {
            echo "⚠️ fullApi.json invalid—skipping widgets\n";
        } else {
            $pdoApp = get_db();
            $pdoApp->beginTransaction();
            $cnt = 0;
            foreach ($spec['paths'] as $path => $ops) {
                foreach ($ops as $m => $info) {
                    $name = $info['operationId'] 
                          ?? strtoupper($m) . str_replace(['/','{','}'],'_',$path);
                    $disp = $info['summary'] ?? $name;
                    $desc = $info['description'] ?? '';
                    $cat  = strtok(trim($path,'/'),'/') ?: 'core';
                    $ep   = 'mps_proxy.php?endpoint=' . urlencode($path);
                    $pr   = [];
                    foreach ($info['parameters'] ?? [] as $param) {
                        $pr[$param['name']] = "{{{$param['name']}}}";
                    }
                    $stmt = $pdoApp->prepare("
                        INSERT IGNORE INTO widgets
                          (name,display_name,description,category,endpoint,params,method,permission)
                        VALUES (?,?,?,?,?,?,?,'view_widgets')
                    ");
                    $stmt->execute([$name,$disp,$desc,$cat,$ep,json_encode($pr),strtolower($m)]);
                    $cnt++;
                }
            }
            $pdoApp->commit();
            echo "✅ Seeded {$cnt} widgets\n";
        }
    } catch (Exception $e) {
        if (isset($pdoApp) && $pdoApp->inTransaction()) $pdoApp->rollBack();
        fatal($e->getMessage());
    }
    echo "</pre>";
    header('Location:?step=2');
    exit;
}

// STEP 2: redirect to verification
if ($step === '2') {
    header('Location:?step=3');
    exit;
}

// STEP 3: verification + self-delete
if ($step === '3') {
    echo "<div class='card'><h2>Verification</h2><pre>";
    $tests = [
        'get_db()'          => fn() => get_db() instanceof PDO,
        'debug_log()'       => fn() => debug_log('Installer OK', [], 'INFO') === null,
        'login_user()'      => function() {
                                   if (!login_user(getenv('DEFAULT_ADMIN_USER'), getenv('DEFAULT_ADMIN_PASS'))) {
                                     throw new Exception('Authentication failed');
                                   }
                                   logout_user();
                                   return true;
                               },
        'get_user_widgets()'=> fn() => is_array(get_user_widgets()),
        'fetch_mps_api()'   => function() {
                                   $r = get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
                                   if (!$r) throw new Exception('No widgets found');
                                   parse_str(parse_url($r['endpoint'], PHP_URL_QUERY), $q);
                                   $d = fetch_mps_api($q['endpoint'], $q);
                                   if (!is_array($d)) throw new Exception('Bad API response');
                                   return true;
                               },
    ];
    foreach ($tests as $lbl => $fn) {
        echo h($lbl) . ': ';
        try { echo $fn() === true ? "✅ PASS\n" : "❌ FAIL\n"; }
        catch (Exception $e) { echo "❌ " . h($e->getMessage()) . "\n"; }
    }
    echo "</pre><p>Cleaning up…</p>";
    @unlink(__FILE__) 
      ? print("✅ Installer removed") 
      : print("⚠️ Could not delete installer");
    echo "</div><script>setTimeout(()=>location='login.php',2000);</script>";
    exit;
}

// Should never reach here
fatal('Unknown installation step.');
