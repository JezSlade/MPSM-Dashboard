<?php
// install.php — Manual‐Entry Installer with Diagnostics Link

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');

$envPath   = __DIR__ . '/.env';
$debugFile = __DIR__ . '/logs/debug.log';

// Helpers
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fatal($m){ echo "<div style='color:red;padding:1rem;background:#fee;'>Fatal: ".h($m)."</div>"; exit; }
function atomicWrite($path,$data){ return file_put_contents($path,$data)!==false; }

// Clear old debug log
if (file_exists($debugFile) && !isset($_GET['step'])) {
    @unlink($debugFile);
}

// Which step?
$step = $_GET['step'] ?? '1';
if (!in_array($step,['1','2','3'],true)) $step='1';

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer (Step <?= h($step) ?>)</title>
  <style>
    body { font-family:Arial,sans-serif;background:#f5f5f5;padding:1rem }
    .card { max-width:700px;margin:auto;background:#fff;padding:1rem;box-shadow:0 0 8px rgba(0,0,0,0.1) }
    .btn  { padding:.5rem 1rem;background:#007bff;color:#fff;border:none;cursor:pointer }
    input { width:100%;padding:.5rem;margin:.5rem 0 }
    h2    { margin-top:1rem }
    .topnav { text-align:right;margin-bottom:1rem }
    .topnav a { text-decoration:none;color:#007bff;margin-left:1rem }
    pre   { background:#eee;padding:1rem;overflow:auto }
  </style>
</head>
<body>
<div class="card">

  <div class="topnav">
    Step <?= h($step) ?>/3
    <a href="diagnostics.php" target="_blank">Run Diagnostics</a>
  </div>

  <details open>
    <summary>Debug Console</summary>
    <pre><?= file_exists($debugFile) ? h(file_get_contents($debugFile)) : '— no debug entries yet —' ?></pre>
  </details>

<?php if ($step==='1'): 
    // Prereq checks
    $checks = [
      'PHP ≥ 7.4'        => version_compare(PHP_VERSION,'7.4.0','>='),
      'PDO MySQL'        => extension_loaded('pdo_mysql'),
      'cURL'             => extension_loaded('curl'),
      'JSON'             => extension_loaded('json'),
      'Writable logs/'   => is_writable(__DIR__.'/logs'),
      'Writable project' => is_writable(__DIR__),
    ];
    echo "<h2>Step 1: Configuration</h2><ul>";
    $all=true;
    foreach($checks as $lbl=>$ok){
      echo "<li>".($ok?'✅':'❌')." ".h($lbl)."</li>";
      $all = $all && $ok;
    }
    echo "</ul>";
    if (!$all) fatal('Fix the above prerequisites and reload.');
?>
  <form method="POST" action="?step=1">
    <h2>Database</h2>
    <input name="DB_HOST" placeholder="DB_HOST" required>
    <input name="DB_NAME" placeholder="DB_NAME" required>
    <input name="DB_USER" placeholder="DB_USER" required>
    <input type="password" name="DB_PASS" placeholder="DB_PASS">

    <h2>API</h2>
    <input name="CLIENT_ID" placeholder="API Client ID" required>
    <input type="password" name="CLIENT_SECRET" placeholder="API Client Secret" required>
    <input name="API_USER" placeholder="API Username">
    <input type="password" name="API_PASS" placeholder="API Password">
    <input name="SCOPE" placeholder="API Scope" required>
    <input name="TOKEN_URL" placeholder="API Token URL" required>
    <input name="BASE_URL" placeholder="API Base URL" required>

    <h2>Admin</h2>
    <input name="ADMIN_USER" placeholder="Admin Username">
    <input type="password" name="ADMIN_PASS" placeholder="Admin Password">

    <button class="btn">Save & Continue →</button>
  </form>
</div>
</body>
</html>
<?php exit; endif; ?>

<?php
// STEP 1 POST: write .env, create DB, run bootstrap & seed
if ($_SERVER['REQUEST_METHOD']==='POST' && $step==='1') {
  echo "<pre>";
  try {
    // Build .env
    $keys = ['DB_HOST','DB_NAME','DB_USER','DB_PASS',
             'CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS',
             'SCOPE','TOKEN_URL','BASE_URL',
             'ADMIN_USER','ADMIN_PASS'];
    $lines = [];
    foreach($keys as $k){
      if(!isset($_POST[$k])) throw new Exception("$k missing");
      $v = str_replace(["\r","\n"],'',$_POST[$k]);
      $lines[] = "$k={$v}";
    }
    if (!atomicWrite($envPath, implode("\n",$lines)."\n")) {
      throw new Exception("Could not write .env");
    }
    echo "✅ .env written\n";

    // Create DB
    $h=$_POST['DB_HOST']; $u=$_POST['DB_USER']; $p=$_POST['DB_PASS']; $d=$_POST['DB_NAME'];
    $pdo = new PDO("mysql:host={$h};charset=utf8mb4",$u,$p,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$d}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database {$d} ready\n";

    // Bootstrap + seed
    require __DIR__.'/core/bootstrap.php';
    echo "✅ Migrations & admin seeded\n";

    // Seed widgets
    $spec = @json_decode(@file_get_contents(__DIR__.'/fullApi.json'),true);
    if (!is_array($spec['paths'] ?? null)) {
      echo "⚠️ fullApi.json invalid—skipping widgets\n";
    } else {
      $pdoApp = get_db();
      $pdoApp->beginTransaction();
      $cnt=0;
      foreach($spec['paths'] as $path=>$ops){
        foreach($ops as $m=>$info){
          $name    = $info['operationId'] ?? strtoupper($m).str_replace(['/','{','}'],'_',$path);
          $disp    = $info['summary']     ?? $name;
          $desc    = $info['description'] ?? '';
          $cat     = strtok(trim($path,'/'),'/') ?: 'core';
          $ep      = 'mps_proxy.php?endpoint='.urlencode($path);
          $pr=[]; foreach($info['parameters']??[] as $param) $pr[$param['name']]="{{{$param['name']}}}";
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
  } catch(Exception $e) {
    if(isset($pdoApp) && $pdoApp->inTransaction()) $pdoApp->rollBack();
    fatal("Error: ".$e->getMessage());
  }
  echo "</pre>";
  header('Location:?step=2');
  exit;
}

// STEP 2 → redirect
if ($step==='2'){
  header('Location:?step=3'); exit;
}

// STEP 3 → verification + self-delete
if ($step==='3'){
  echo "<div class='card'><h2>Verification</h2><pre>";
  $tests = [
    'get_db()'          => fn()=>get_db() instanceof PDO,
    'debug_log()'       => fn()=>debug_log('OK',[], 'INFO')===null,
    'login_user()'      => function(){
                             if(!login_user(getenv('DEFAULT_ADMIN_USER'),getenv('DEFAULT_ADMIN_PASS')))
                               throw new Exception('Auth failed');
                             logout_user(); return true;
                          },
    'get_user_widgets()'=> fn()=>is_array(get_user_widgets()),
    'fetch_mps_api()'   => function(){
                             $r = get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
                             if(!$r) throw new Exception('No widgets');
                             parse_str(parse_url($r['endpoint'],PHP_URL_QUERY),$q);
                             $d = fetch_mps_api($q['endpoint'],$q);
                             if(!is_array($d)) throw new Exception('Bad API');
                             return true;
                          },
  ];
  foreach($tests as $lbl=>$fn){
    echo h($lbl).': ';
    try{ echo $fn()===true?'✅ PASS':'❌ FAIL'; }
    catch(Exception $e){ echo "❌ ".$e->getMessage(); }
    echo "\n";
  }
  echo "</pre><p>Cleaning up…</p>";
  @unlink(__FILE__)?print("✅ Deleted installer"):print("⚠️ Could not delete");
  echo "</div><script>setTimeout(()=>location='login.php',2000);</script>";
  exit;
}

fatal('Unknown step');
