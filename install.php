<?php
// install.php — Battle-tested, Debug-First, Atomic Installer with Fully Auto-Populated Form

session_start();

// 1) Display every error immediately
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

// Determine installer step (1,2,3)
$step = $_GET['step'] ?? '1';
if (!in_array($step, ['1','2','3'], true)) $step = '1';

// Render <head> + debug console
echo <<<'HTML'
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer</title>
  <link href="https://fonts.googleapis.com/css2?family=Consales&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Silence Tailwind CDN prod warning
    (function(){const w=console.warn;console.warn=(...a)=>{if(!a[0].includes('cdn.tailwindcss.com should'))w(...a)}})();
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
  <pre>
HTML;
if (file_exists($debugFile)) {
    echo h(file_get_contents($debugFile));
} else {
    echo "— no debug entries yet —";
}
echo <<<'HTML'
  </pre>
</details>

<div class="card">
  <div class="steps">
    <div class="step <?= $step==='1'?'active':'' ?>">1. Configure</div>
    <div class="step <?= $step==='2'?'active':'' ?>">2. Migrate</div>
    <div class="step <?= $step==='3'?'active':'' ?>">3. Verify</div>
  </div>
HTML;

// ──────────────────────────────────────
// STEP 1: Prerequisites + Auto-Populated Form
// ──────────────────────────────────────
if ($step === '1') {
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
    foreach ($checks as $lbl => $ok) {
        echo "<li>".($ok?'✅':'❌')." ".h($lbl)."</li>";
        if (!$ok) $allOK = false;
    }
    echo "</ul>";
    if (!$allOK) {
      fatal('Fix prerequisites and reload.');
    }

    // Auto-populated form
    echo "<form method='POST' action='?step=1'>";
      echo "<h3>Database</h3>";
      echo "<input name='DB_HOST' placeholder='DB_HOST' value='localhost' required>";
      echo "<input name='DB_NAME' placeholder='DB_NAME' value='resolut7_mpsm' required>";
      echo "<input name='DB_USER' placeholder='DB_USER' value='resolut7_mpsm' required>";
      echo "<input name='DB_PASS' type='password' placeholder='DB_PASS' value='MP\$M_Nr0lr' required>";

      echo "<h3>MPS API</h3>";
      echo "<input name='CLIENT_ID'     placeholder='API Client ID'     value='your_client_id'        required>";
      echo "<input name='CLIENT_SECRET' placeholder='API Client Secret' value='your_client_secret'    required>";
      echo "<input name='API_USER'      placeholder='API Username'      value='your_api_username'     required>";
      echo "<input name='API_PASS' type='password' placeholder='API Password'   value='your_api_password'     required>";
      echo "<input name='SCOPE'         placeholder='API Scope'        value='account'               required>";
      echo "<input name='TOKEN_URL'     placeholder='API Token URL'    value='https://api.abassetmanagement.com/api3/token' required>";
      echo "<input name='BASE_URL'      placeholder='API Base URL'     value='https://api.abassetmanagement.com/api3/'     required>";

      echo "<h3>Default Admin</h3>";
      echo "<input name='ADMIN_USER' placeholder='Admin Username' value='admin'      required>";
      echo "<input name='ADMIN_PASS' type='password' placeholder='Admin Password' value='changeme' required>";

      echo "<button class='btn'>Save & Continue →</button>";
    echo "</form>";

    echo "</div></body></html>";
    exit;
}

// ────────────────────────────────────────────────
// STEP 1 POST: Write .env (using form values), Create DB, Bootstrap & Seed
// ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '1') {
    echo "<h2 class='text-xl mb-4'>Applying Configuration…</h2><pre>";
    try {
        // Build env from form
        $keys = [
          'DB_HOST','DB_NAME','DB_USER','DB_PASS',
          'CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS',
          'SCOPE','TOKEN_URL','BASE_URL'
        ];
        $lines = [];
        foreach ($keys as $k) {
            if (empty($_POST[$k])) throw new Exception("$k is required");
            $v = str_replace(["\r","\n"], '', $_POST[$k]);
            $lines[] = "$k={$v}";
        }
        array_push($lines,
          "ENVIRONMENT=production",
          "DEBUG=false",
          "DEFAULT_ADMIN_USER=".str_replace(["\r","\n"],'',$\$_POST['ADMIN_USER']),
          "DEFAULT_ADMIN_PASS=".str_replace(["\r","\n"],'',$\$_POST['ADMIN_PASS'])
        );
        if (!atomicWrite(__DIR__.'/.env', implode("\n",$lines)."\n")) {
            throw new Exception("Failed to write .env");
        }
        echo "✅ .env written\n";

        // Create DB if missing with real PDO logic
        $h = getenv('DB_HOST'); $u = getenv('DB_USER'); $p = getenv('DB_PASS'); $d = getenv('DB_NAME');
        $pdoRoot = new PDO("mysql:host={$h};charset=utf8mb4", $u, $p, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$d}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database `{$d}` ready\n";

        // Bootstrap migrations & admin seed
        require __DIR__ . '/core/bootstrap.php';
        echo "✅ Migrations & admin seeded\n";

        // Transactional widget seeding from fullApi.json
        $spec = @json_decode(@file_get_contents(__DIR__.'/fullApi.json'), true);
        if (!is_array($spec['paths'] ?? null)) {
            echo "⚠️ fullApi.json missing/invalid — skipping widgets\n";
        } else {
            $pdo = get_db();
            $pdo->beginTransaction();
            $cnt = 0;
            foreach ($spec['paths'] as $path => $ops) {
                foreach ($ops as $m => $info) {
                    $name     = $info['operationId'] ?? strtoupper($m).str_replace(['/','{','}'],'_',$path);
                    $display  = $info['summary']   ?? $name;
                    $desc     = $info['description'] ?? '';
                    $category = strtok(trim($path,'/'),'/') ?: 'core';
                    $endpoint = 'mps_proxy.php?endpoint='.urlencode($path);
                    $pr = [];
                    foreach ($info['parameters'] ?? [] as $param) {
                        $pr[$param['name']] = "{{{$param['name']}}}";
                    }
                    $pjson = json_encode($pr);
                    $stmt = $pdo->prepare("
                      INSERT IGNORE INTO widgets
                        (name,display_name,description,category,endpoint,params,method,permission)
                      VALUES (?,?,?,?,?,?,?,'view_widgets')
                    ");
                    $stmt->execute([$name,$display,$desc,$category,$endpoint,$pjson,strtolower($m)]);
                    $cnt++;
                }
            }
            $pdo->commit();
            echo "✅ Seeded {$cnt} widgets\n";
        }
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        fatal("Setup error: " . $e->getMessage());
    }
    echo "</pre><script>setTimeout(()=>location='?step=2',1200);</script>";
    echo "</div></body></html>";
    exit;
}

// ────────────────────────────────────────────
// STEP 2: Redirect immediately to STEP 3
// ────────────────────────────────────────────
if ($step === '2') {
    header('Location:?step=3');
    exit;
}

// ──────────────────────────────────────────────
// STEP 3: Verification Suite + Self-Delete
// ──────────────────────────────────────────────
if ($step === '3') {
    echo "<h2 class='text-xl mb-4'>Verification</h2><pre>";
    $tests = [
      'get_db()'          => fn()=>get_db() instanceof PDO,
      'debug_log()'       => fn()=>debug_log('Installer test',[], 'INFO') === null,
      'login_user()'      => function(){
                                $u=getenv('DEFAULT_ADMIN_USER');
                                $p=getenv('DEFAULT_ADMIN_PASS');
                                if (!login_user($u,$p)) throw new Exception('login failed');
                                logout_user(); return true;
                             },
      'get_user_widgets()'=> fn()=>is_array(get_user_widgets()),
      'fetch_mps_api()'   => function(){
                                $row = get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
                                if (!$row) throw new Exception('no widgets');
                                parse_str(parse_url($row['endpoint'],PHP_URL_QUERY), $qs);
                                $data = fetch_mps_api($qs['endpoint'], $qs);
                                if (!is_array($data)) throw new Exception('bad response');
                                return true;
                             },
    ];
    foreach ($tests as $lbl => $fn) {
        echo h($lbl) . ': ';
        try {
            $ok = $fn();
            echo $ok === true ? "✅ PASS\n" : "❌ FAIL\n";
        } catch (Exception $e) {
            echo "❌ ERROR: " . h($e->getMessage()) . "\n";
        }
    }
    echo "</pre><pre>Cleaning up…</pre>";
    @unlink(__FILE__) ? print("✅ Installer removed\n") : print("⚠️ Not deleted\n");
    echo "</div><script>setTimeout(()=>location='login.php',2000);</script>";
    exit;
}

// Fallback
fatal('Unknown installer step.');
