<?php
// install.php — Full-Featured, Auto-Populating, Self-Healing Installer

session_start();
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');

define('INSTALLER', true);

$envPath   = __DIR__.'/.env';
$debugFile = __DIR__.'/logs/debug.log';

// Helpers
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fatal($msg){
    echo "<div style='color:red;padding:1rem;background:#fee;border:1px solid #f00;'>Fatal: ".h($msg)."</div></body></html>";
    exit;
}
function atomicWrite($path, $data, $mode = 0600){
    $tmp = $path.'.tmp';
    if (file_put_contents($tmp, $data) === false) return false;
    chmod($tmp, $mode);
    return rename($tmp, $path);
}
function parseEnv($path){
    $lines = @file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [];
    $out = [];
    foreach($lines as $line){
        $line = trim($line);
        if ($line === '' || $line[0]==='#') continue;
        if (strpos($line,'=')!==false){
            list($k,$v) = explode('=', $line, 2);
            $out[trim($k)] = trim($v);
        }
    }
    return $out;
}

// Clear old debug
if (file_exists($debugFile) && !isset($_GET['step'])) {
    @unlink($debugFile);
}

// Default values
$defaults = [
    'DB_HOST'       => 'localhost',
    'DB_NAME'       => 'resolut7_mpsm',
    'DB_USER'       => 'resolut7_mpsm',
    'DB_PASS'       => 'MP$M_Nr0lr',
    'CLIENT_ID'     => '',
    'CLIENT_SECRET' => '',
    'API_USER'      => '',
    'API_PASS'      => '',
    'SCOPE'         => 'account',
    'TOKEN_URL'     => 'https://api.abassetmanagement.com/api3/token',
    'BASE_URL'      => 'https://api.abassetmanagement.com/api3/',
    'ADMIN_USER'    => 'admin',
    'ADMIN_PASS'    => 'changeme',
];

// Override with existing .env
if (file_exists($envPath)){
    $env = parseEnv($envPath);
    foreach($env as $k=>$v){
        if (array_key_exists($k, $defaults)){
            $defaults[$k] = $v;
        }
    }
}

// Determine step
$step = $_GET['step'] ?? '1';
if (!in_array($step, ['1','2','3'], true)) {
    $step = '1';
}

// HTML head + debug console
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 1rem; }
    .card { max-width: 800px; margin: auto; background: #fff; padding:1rem; box-shadow:0 0 10px rgba(0,0,0,0.1); }
    .btn { padding:0.5rem 1rem; margin-top:1rem; background:#28a; color:#fff; border:none; cursor:pointer; }
    pre { background:#eee; padding:1rem; overflow:auto; }
    input { width:100%; padding:0.5rem; margin:0.5rem 0; }
    h2 { margin-top:1rem; }
  </style>
</head>
<body>
<div class="card">
  <h1>Installer (Step <?= h($step) ?>)</h1>

  <details open>
    <summary>Debug Console</summary>
    <pre><?= file_exists($debugFile) ? h(file_get_contents($debugFile)) : '— no debug entries yet —' ?></pre>
  </details>

<?php
// STEP 1: Show form
if ($step === '1'):
    // Prereq checks
    $checks = [
        'PHP Version ≥ 7.4'   => version_compare(PHP_VERSION,'7.4.0','>='),
        'PDO MySQL Loaded'    => extension_loaded('pdo_mysql'),
        'cURL Loaded'         => extension_loaded('curl'),
        'JSON Loaded'         => extension_loaded('json'),
        'Writable logs/'      => is_writable(__DIR__.'/logs'),
        'Writable project/'   => is_writable(__DIR__),
    ];
    echo "<h2>Prerequisites</h2><ul>";
    $all = true;
    foreach ($checks as $lbl=>$ok){
        echo "<li>".($ok?'✅':'❌')." ".h($lbl)."</li>";
        $all = $all && $ok;
    }
    echo "</ul>";
    if (!$all) {
        fatal('Fix the above and reload.');
    }
?>
  <form method="POST" action="?step=1">
    <h2>Database Configuration</h2>
    <label>DB_HOST*<input name="DB_HOST" value="<?=h($defaults['DB_HOST'])?>" required></label>
    <label>DB_NAME*<input name="DB_NAME" value="<?=h($defaults['DB_NAME'])?>" required></label>
    <label>DB_USER*<input name="DB_USER" value="<?=h($defaults['DB_USER'])?>" required></label>
    <label>DB_PASS<input type="password" name="DB_PASS" value="<?=h($defaults['DB_PASS'])?>"></label>

    <h2>API Configuration</h2>
    <label>CLIENT_ID*<input name="CLIENT_ID" value="<?=h($defaults['CLIENT_ID'])?>" required></label>
    <label>CLIENT_SECRET*<input type="password" name="CLIENT_SECRET" value="<?=h($defaults['CLIENT_SECRET'])?>" required></label>
    <label>API_USER<input name="API_USER" value="<?=h($defaults['API_USER'])?>"></label>
    <label>API_PASS<input type="password" name="API_PASS" value="<?=h($defaults['API_PASS'])?>"></label>
    <label>SCOPE*<input name="SCOPE" value="<?=h($defaults['SCOPE'])?>" required></label>
    <label>TOKEN_URL*<input name="TOKEN_URL" value="<?=h($defaults['TOKEN_URL'])?>" required></label>
    <label>BASE_URL*<input name="BASE_URL" value="<?=h($defaults['BASE_URL'])?>" required></label>

    <h2>Admin Account</h2>
    <label>ADMIN_USER<input name="ADMIN_USER" value="<?=h($defaults['ADMIN_USER'])?>"></label>
    <label>ADMIN_PASS<input type="password" name="ADMIN_PASS" value="<?=h($defaults['ADMIN_PASS'])?>"></label>

    <button class="btn">Save & Continue</button>
  </form>
</div></body></html>
<?php
    exit;
endif;

// STEP 1 POST: write .env, create DB, bootstrap & seed
if ($_SERVER['REQUEST_METHOD']==='POST' && $step==='1'){
    echo "<pre>";
    try {
        // Build .env
        $keys = ['DB_HOST','DB_NAME','DB_USER','DB_PASS','CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS','SCOPE','TOKEN_URL','BASE_URL','ADMIN_USER','ADMIN_PASS'];
        $lines = [];
        foreach ($keys as $k){
            if (!isset($_POST[$k])) throw new Exception("$k missing");
            $v = str_replace(["\r","\n"], '', $_POST[$k]);
            $lines[] = "$k={$v}";
        }
        $content = implode("\n",$lines)."\n";
        if (!atomicWrite($envPath, $content)) throw new Exception("Failed to write .env");
        echo "✅ .env written\n";

        // Create DB if not exists
        $h=$_POST['DB_HOST']; $u=$_POST['DB_USER']; $p=$_POST['DB_PASS']; $d=$_POST['DB_NAME'];
        $pdoRoot = new PDO("mysql:host={$h};charset=utf8mb4",$u,$p,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$d}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database `{$d}` ready\n";

        // Bootstrap migrations & seed admin
        require __DIR__.'/core/bootstrap.php';
        echo "✅ Migrations & admin seeded\n";

        // Seed widgets from fullApi.json
        $spec = @json_decode(@file_get_contents(__DIR__.'/fullApi.json'), true);
        if (!is_array($spec['paths'] ?? null)) {
            echo "⚠️ fullApi.json invalid, skipping widgets\n";
        } else {
            $pdo = get_db(); $pdo->beginTransaction(); $cnt=0;
            foreach ($spec['paths'] as $path=>$ops){
                foreach ($ops as $m=>$info){
                    $name    = $info['operationId'] ?? strtoupper($m).str_replace(['/','{','}'],'_',$path);
                    $disp    = $info['summary'] ?? $name;
                    $desc    = $info['description'] ?? '';
                    $cat     = strtok(trim($path,'/'),'/') ?: 'core';
                    $ep      = 'mps_proxy.php?endpoint='.urlencode($path);
                    $pr=[]; foreach ($info['parameters']??[] as $p) $pr[$p['name']]="{{{$p['name']}}}";
                    $stmt = $pdo->prepare("
                      INSERT IGNORE INTO widgets
                        (name,display_name,description,category,endpoint,params,method,permission)
                      VALUES (?,?,?,?,?,?,?,'view_widgets')
                    ");
                    $stmt->execute([$name,$disp,$desc,$cat,$ep,json_encode($pr),strtolower($m)]);
                    $cnt++;
                }
            }
            $pdo->commit();
            echo "✅ Seeded {$cnt} widgets\n";
        }

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        fatal($e->getMessage());
    }
    echo "</pre>";
    header('Location:?step=2');
    exit;
}

// STEP 2: redirect to verification
if ($step==='2'){
    header('Location:?step=3');
    exit;
}

// STEP 3: run tests and self-delete
if ($step==='3'){
    echo "<div class='card'><h2>Verification</h2><pre>";
    $tests = [
        'get_db()'          => fn()=>get_db() instanceof PDO,
        'debug_log()'       => fn()=>debug_log('Installer test',[], 'INFO')===null,
        'login_user()'      => function(){
            if (!login_user(getenv('DEFAULT_ADMIN_USER'),getenv('DEFAULT_ADMIN_PASS'))) throw new Exception('Auth failed');
            logout_user(); return true;
        },
        'get_user_widgets()'=> fn()=>is_array(get_user_widgets()),
        'fetch_mps_api()'   => function(){
            $r = get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
            if (!$r) throw new Exception('No widgets');
            parse_str(parse_url($r['endpoint'],PHP_URL_QUERY),$q);
            $d = fetch_mps_api($q['endpoint'],$q);
            if (!is_array($d)) throw new Exception('Bad response');
            return true;
        },
    ];
    foreach ($tests as $lbl=>$fn){
        echo h($lbl).': ';
        try { echo $fn()===true ? "✅ PASS\n" : "❌ FAIL\n"; }
        catch (Exception $e) { echo "❌ ".$e->getMessage()."\n"; }
    }
    echo "</pre><p>Cleaning up...</p>";
    @unlink(__FILE__) ? print("✅ Installer removed") : print("⚠️ Could not delete installer");
    echo "</div>";
    echo "<script>setTimeout(()=>location='login.php',2000);</script>";
    exit;
}

fatal('Unknown installation step.');
