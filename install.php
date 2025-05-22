<?php
// install.php — Battle-tested, Debug-First, Atomic Installer with Auto-Populate Fix

session_start();

// 1) Show all PHP errors immediately
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// 2) Clear old debug log on fresh load
$debugFile = __DIR__ . '/logs/debug.log';
if (file_exists($debugFile) && !isset($_GET['step'])) {
    @unlink($debugFile);
}

// Helpers
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fatal($msg){
    echo "<div class='text-red-600 mt-4'>Fatal: ".h($msg)."</div></div></body></html>";
    exit;
}
function atomicWrite(string $path, string $data, int $mode = 0600): bool {
    $tmp = $path.'.tmp';
    if (file_put_contents($tmp, $data) === false) return false;
    chmod($tmp, $mode);
    return rename($tmp, $path);
}

// Default form values for debugging
$defaults = [
  'DB_HOST'      => 'localhost',
  'DB_NAME'      => 'resolut7_mpsm',
  'DB_USER'      => 'resolut7_mpsm',
  'DB_PASS'      => 'MP$M_Nr0lr',
  'CLIENT_ID'    => 'your_client_id',
  'CLIENT_SECRET'=> 'your_client_secret',
  'API_USER'     => 'your_api_username',
  'API_PASS'     => 'your_api_password',
  'SCOPE'        => 'account',
  'TOKEN_URL'    => 'https://api.abassetmanagement.com/api3/token',
  'BASE_URL'     => 'https://api.abassetmanagement.com/api3/',
  'ADMIN_USER'   => 'admin',
  'ADMIN_PASS'   => 'changeme',
];

// Determine step (1,2,3)
$step = $_GET['step'] ?? '1';
if (!in_array($step, ['1','2','3'], true)) $step = '1';

// Render head + debug console
?><!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer</title>
  <link href="https://fonts.googleapis.com/css2?family=Consales&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Silence Tailwind CDN warning
    (function(){
      const w = console.warn;
      console.warn = (...a)=>{ if(!a[0].includes('cdn.tailwindcss.com should')) w(...a); };
    })();
  </script>
  <style>
    body{font-family:'Consales',sans-serif;background:#f0f0f3;padding:1rem}
    .card{max-width:700px;margin:2rem auto;padding:2rem;background:#f0f0f3;border-radius:.5rem;
          box-shadow:4px 4px 8px rgba(0,0,0,0.2),-4px -4px 8px rgba(255,255,255,0.7)}
    .btn{display:inline-block;padding:.75rem 1.5rem;margin-top:1rem;
         background:#e0e5ec;border-radius:.5rem;
         box-shadow:2px 2px 5px rgba(0,0,0,0.1),-2px -2px 5px rgba(255,255,255,0.7);
         cursor:pointer}
    .steps{display:flex;margin-bottom:1.5rem}
    .step{flex:1;text-align:center;padding:.5rem;border-radius:.25rem}
    .active{background:#4f46e5;color:#fff}
    pre{background:#fff;padding:1rem;border-radius:.5rem;overflow:auto;margin:1rem 0}
    input,select{width:100%;padding:.5rem;margin:.5rem 0;border:1px solid #ccc;border-radius:.25rem}
    details{margin-bottom:1rem;background:#fff;border-radius:.5rem;padding:.5rem}
    summary{font-weight:bold;cursor:pointer}
  </style>
</head>
<body>

<details open>
  <summary>Debug Console</summary>
  <pre><?php
    if (file_exists($debugFile)) {
        echo h(file_get_contents($debugFile));
    } else {
        echo "— no debug entries yet —";
    }
  ?></pre>
</details>

<div class="card">
  <div class="steps">
    <div class="step <?= $step==='1'?'active':'' ?>">1. Configure</div>
    <div class="step <?= $step==='2'?'active':'' ?>">2. Migrate</div>
    <div class="step <?= $step==='3'?'active':'' ?>">3. Verify</div>
  </div>

<?php if ($step === '1'): 
    // Prerequisite checks
    $checks = [
      'PHP ≥ 7.4'        => version_compare(PHP_VERSION,'7.4.0','>='),
      'PDO MySQL'        => extension_loaded('pdo_mysql'),
      'cURL'             => extension_loaded('curl'),
      'JSON'             => extension_loaded('json'),
      'Writable /logs'   => is_writable(__DIR__.'/logs'),
      'Writable project' => is_writable(__DIR__),
    ];
    echo "<h2 class='text-xl mb-4'>Step 1: Configuration</h2><ul>";
    $allOK = true;
    foreach ($checks as $lbl=>$ok) {
        echo "<li>" . ($ok ? '✅' : '❌') . " " . h($lbl) . "</li>";
        if (!$ok) $allOK = false;
    }
    echo "</ul>";
    if (!$allOK) { fatal('Fix prerequisites and reload.'); }
?>
  <form method="POST" action="?step=1">
    <h3>Database</h3>
    <input name="DB_HOST" value="<?=h($defaults['DB_HOST'])?>" placeholder="DB_HOST" required>
    <input name="DB_NAME" value="<?=h($defaults['DB_NAME'])?>" placeholder="DB_NAME" required>
    <input name="DB_USER" value="<?=h($defaults['DB_USER'])?>" placeholder="DB_USER" required>
    <input name="DB_PASS" type="password" value="<?=h($defaults['DB_PASS'])?>" placeholder="DB_PASS" required>

    <h3>MPS API</h3>
    <input name="CLIENT_ID"     value="<?=h($defaults['CLIENT_ID'])?>"     placeholder="API Client ID"     required>
    <input name="CLIENT_SECRET" value="<?=h($defaults['CLIENT_SECRET'])?>" placeholder="API Client Secret" required>
    <input name="API_USER"      value="<?=h($defaults['API_USER'])?>"      placeholder="API Username"      required>
    <input name="API_PASS" type="password" value="<?=h($defaults['API_PASS'])?>" placeholder="API Password" required>
    <input name="SCOPE"         value="<?=h($defaults['SCOPE'])?>"         placeholder="API Scope"        required>
    <input name="TOKEN_URL"     value="<?=h($defaults['TOKEN_URL'])?>"     placeholder="API Token URL"    required>
    <input name="BASE_URL"      value="<?=h($defaults['BASE_URL'])?>"      placeholder="API Base URL"     required>

    <h3>Default Admin</h3>
    <input name="ADMIN_USER" value="<?=h($defaults['ADMIN_USER'])?>" placeholder="Admin Username" required>
    <input name="ADMIN_PASS" type="password" value="<?=h($defaults['ADMIN_PASS'])?>" placeholder="Admin Password" required>

    <button class="btn">Save →</button>
  </form>
<?php
  exit;
endif;

// ─────────────────────────────────────────────
// STEP 1 POST: write .env, create DB, run bootstrap & seed
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && $step==='1') {
    echo "<h2 class='text-xl mb-4'>Applying Configuration…</h2><pre>";
    try {
        // Build and write .env
        $keys = ['DB_HOST','DB_NAME','DB_USER','DB_PASS',
                 'CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS',
                 'SCOPE','TOKEN_URL','BASE_URL'];
        $lines = [];
        foreach ($keys as $k) {
            if (empty($_POST[$k])) throw new Exception("$k required");
            $v = str_replace(["\r","\n"],'',$_POST[$k]);
            $lines[] = "$k={$v}";
        }
        array_push($lines,
          "ENVIRONMENT=production","DEBUG=false",
          "DEFAULT_ADMIN_USER=".str_replace(["\r","\n"],'',$defaults['ADMIN_USER']),
          "DEFAULT_ADMIN_PASS=".str_replace(["\r","\n"],'',$defaults['ADMIN_PASS'])
        );
        if (!atomicWrite(__DIR__.'/.env',implode("\n",$lines)."\n")) {
            throw new Exception("Cannot write .env");
        }
        echo "✅ .env written\n";

        // Create DB if missing
        $h=getenv('DB_HOST'); $u=getenv('DB_USER'); $p=getenv('DB_PASS'); $d=getenv('DB_NAME');
        $pdoRoot=new PDO("mysql:host={$h};charset=utf8mb4",$u,$p,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$d}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database `{$d}` ready\n";

        // Bootstrap migrations & seed
        require __DIR__.'/core/bootstrap.php';
        echo "✅ Migrations & admin seeded\n";

        // Seed widgets
        $spec=@json_decode(@file_get_contents(__DIR__.'/fullApi.json'),true);
        if(!is_array($spec['paths']??null)){
            echo "⚠️ fullApi.json missing—skipping widgets\n";
        } else {
            $pdo=get_db(); $pdo->beginTransaction(); $cnt=0;
            foreach($spec['paths'] as $p=>$ops){
                foreach($ops as $m=>$info){
                    $name    = $info['operationId'] ?? strtoupper($m).str_replace(['/','{','}'],'_',$p);
                    $disp    = $info['summary']     ?? $name;
                    $desc    = $info['description'] ?? '';
                    $cat     = strtok(trim($p,'/'),'/') ?: 'core';
                    $ep      = 'mps_proxy.php?endpoint='.urlencode($p);
                    $params  = [];
                    foreach($info['parameters']??[] as $par){
                        $params[$par['name']]="{{{$par['name']}}}";
                    }
                    $stmt=$pdo->prepare("
                        INSERT IGNORE INTO widgets
                          (name,display_name,description,category,endpoint,params,method,permission)
                        VALUES (?,?,?,?,?,?,?,'view_widgets')
                    ");
                    $stmt->execute([
                        $name,$disp,$desc,$cat,$ep,json_encode($params),strtolower($m)
                    ]);
                    $cnt++;
                }
            }
            $pdo->commit();
            echo "✅ Seeded {$cnt} widgets\n";
        }

    } catch(Exception $e){
        if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
        fatal($e->getMessage());
    }
    echo "</pre><script>setTimeout(()=>location='?step=2',1200);</script>";
    echo "</div></body></html>";
    exit;
}

// STEP 2 → redirect to 3
if($step==='2'){
    header('Location:?step=3');
    exit;
}

// STEP 3: Verification + cleanup
if($step==='3'){
    echo "<h2 class='text-xl mb-4'>Verification</h2><pre>";
    $tests=[
      'get_db()'          => fn()=>get_db() instanceof PDO,
      'debug_log()'       => fn()=>debug_log('Installer OK',[], 'INFO')===null,
      'login_user()'      => function(){
                                $u=getenv('DEFAULT_ADMIN_USER');
                                $p=getenv('DEFAULT_ADMIN_PASS');
                                if(!login_user($u,$p))throw new Exception('auth failed');
                                logout_user();return true;
                             },
      'get_user_widgets()'=> fn()=>is_array(get_user_widgets()),
      'fetch_mps_api()'   => function(){
                                $row=get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
                                if(!$row)throw new Exception('no widgets');
                                parse_str(parse_url($row['endpoint'],PHP_URL_QUERY),$q);
                                $d=fetch_mps_api($q['endpoint'],$q);
                                if(!is_array($d))throw new Exception('bad response');
                                return true;
                             },
    ];
    foreach($tests as $l=>$fn){
        echo h($l).': ';
        try {echo $fn()===true?'✅ PASS':'❌ FAIL';}
        catch(Exception$e){echo "❌ ".$e->getMessage();}
        echo "\n";
    }
    echo "</pre><pre>Cleaning up…</pre>";
    @unlink(__FILE__)?print("✅ Removed\n"):print("⚠️ Not deleted\n");
    echo "</div><script>setTimeout(()=>location='login.php',2000);</script>";
    exit;
}

fatal('Unknown step.');
