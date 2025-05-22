<?php
// install.php — Manual‐Entry, Debug‐First Installer

session_start();

// Show every error
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');

// Paths
$envPath   = __DIR__ . '/.env';
$debugFile = __DIR__ . '/logs/debug.log';

// Helpers
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function fatal($msg) {
    echo "<div style='color:red;padding:1rem;background:#fee;border:1px solid #f00;'>Fatal: " . h($msg) . "</div>";
    exit;
}
function atomicWrite($path, $data, $mode = 0600) {
    $tmp = "$path.tmp";
    if (file_put_contents($tmp, $data) === false) return false;
    chmod($tmp, $mode);
    return rename($tmp, $path);
}

// Clear old debug log on fresh load
if (file_exists($debugFile) && !isset($_GET['step'])) {
    @unlink($debugFile);
}

// Determine current installer step
$step = $_GET['step'] ?? '1';
if (!in_array($step, ['1','2','3'], true)) {
    $step = '1';
}

// Output HTML head + debug console
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installer (Step <?= h($step) ?>)</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 1rem; }
    .card { max-width: 700px; margin: auto; background: #fff; padding: 1rem; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    .btn  { padding: 0.5rem 1rem; background: #007bff; color: #fff; border: none; cursor: pointer; }
    pre   { background: #eee; padding: 1rem; overflow: auto; }
    input { width: 100%; padding: 0.5rem; margin: 0.5rem 0; }
    h2    { margin-top: 1rem; }
  </style>
</head>
<body>
<div class="card">
  <h1>Installer</h1>
  <p><strong>Step <?= h($step) ?> of 3</strong></p>

  <details open>
    <summary>Debug Console</summary>
    <pre><?= file_exists($debugFile) ? h(file_get_contents($debugFile)) : '— no debug entries yet —' ?></pre>
  </details>

<?php if ($step === '1'): 
    // Prerequisites
    $checks = [
        'PHP ≥ 7.4'        => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO MySQL'        => extension_loaded('pdo_mysql'),
        'cURL'             => extension_loaded('curl'),
        'JSON'             => extension_loaded('json'),
        'Writable logs/'   => is_writable(__DIR__ . '/logs'),
        'Writable project' => is_writable(__DIR__),
    ];
    echo "<h2>Prerequisites</h2><ul>";
    $allOk = true;
    foreach ($checks as $label => $ok) {
        echo "<li>" . ($ok ? '✅' : '❌') . " " . h($label) . "</li>";
        if (!$ok) $allOk = false;
    }
    echo "</ul>";
    if (!$allOk) {
        fatal("Please fix the above prerequisites and reload this page.");
    }
?>
  <form method="POST" action="?step=1">
    <h2>Database Configuration</h2>
    <label>DB_HOST*<input name="DB_HOST" placeholder="e.g. localhost" required></label>
    <label>DB_NAME*<input name="DB_NAME" placeholder="e.g. resolut7_mpsm" required></label>
    <label>DB_USER*<input name="DB_USER" placeholder="e.g. resolut7_mpsm" required></label>
    <label>DB_PASS<input type="password" name="DB_PASS" placeholder="Your DB password"></label>

    <h2>API Configuration</h2>
    <label>CLIENT_ID*<input name="CLIENT_ID" placeholder="Your API client ID" required></label>
    <label>CLIENT_SECRET*<input type="password" name="CLIENT_SECRET" placeholder="Your API secret" required></label>
    <label>API_USER<input name="API_USER" placeholder="Optional API username"></label>
    <label>API_PASS<input type="password" name="API_PASS" placeholder="Optional API password"></label>
    <label>SCOPE*<input name="SCOPE" placeholder="e.g. account" required></label>
    <label>TOKEN_URL*<input name="TOKEN_URL" placeholder="e.g. https://api.example.com/token" required></label>
    <label>BASE_URL*<input name="BASE_URL" placeholder="e.g. https://api.example.com/" required></label>

    <h2>Admin Account</h2>
    <label>ADMIN_USER<input name="ADMIN_USER" placeholder="e.g. admin"></label>
    <label>ADMIN_PASS<input type="password" name="ADMIN_PASS" placeholder="e.g. changeme"></label>

    <button class="btn">Save &amp; Continue →</button>
  </form>
</div>
</body>
</html>
<?php
    exit;
endif;

// STEP 1 POST: write .env, create DB, bootstrap & seed widgets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '1') {
    echo "<pre>";
    try {
        // Build .env lines
        $keys = [
            'DB_HOST','DB_NAME','DB_USER','DB_PASS',
            'CLIENT_ID','CLIENT_SECRET','API_USER','API_PASS',
            'SCOPE','TOKEN_URL','BASE_URL',
            'ADMIN_USER','ADMIN_PASS'
        ];
        $lines = [];
        foreach ($keys as $k) {
            if (!isset($_POST[$k])) {
                throw new Exception("$k is required");
            }
            $v = str_replace(["\r","\n"], '', $_POST[$k]);
            $lines[] = "$k={$v}";
        }
        // Write .env
        $content = implode("\n", $lines) . "\n";
        if (!atomicWrite($envPath, $content)) {
            throw new Exception("Failed to write .env");
        }
        echo "✅ .env written\n";

        // Create database if missing
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
            "CREATE DATABASE IF NOT EXISTS `{$d}` 
             CHARACTER SET utf8mb4 
             COLLATE utf8mb4_unicode_ci"
        );
        echo "✅ Database `{$d}` is ready\n";

        // Bootstrap: migrations & default admin seed
        require __DIR__ . '/core/bootstrap.php';
        echo "✅ Migrations & admin seeded\n";

        // Seed widgets from fullApi.json
        $spec = @json_decode(@file_get_contents(__DIR__ . '/fullApi.json'), true);
        if (!is_array($spec['paths'] ?? null)) {
            echo "⚠️ fullApi.json missing or invalid—skipping widget seed\n";
        } else {
            $pdo = get_db();
            $pdo->beginTransaction();
            $count = 0;
            foreach ($spec['paths'] as $path => $ops) {
                foreach ($ops as $m => $info) {
                    $name     = $info['operationId'] 
                                ?? strtoupper($m) . str_replace(['/','{','}'],'_',$path);
                    $display  = $info['summary'] ?? $name;
                    $desc     = $info['description'] ?? '';
                    $category = strtok(trim($path, '/'), '/') ?: 'core';
                    $endpoint = 'mps_proxy.php?endpoint=' . urlencode($path);
                    $params   = [];
                    foreach ($info['parameters'] ?? [] as $param) {
                        $params[$param['name']] = "{{{$param['name']}}}";
                    }
                    $pjson = json_encode($params);
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO widgets
                          (name, display_name, description, category, endpoint, params, method, permission)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'view_widgets')
                    ");
                    $stmt->execute([
                        $name,
                        $display,
                        $desc,
                        $category,
                        $endpoint,
                        $pjson,
                        strtolower($m)
                    ]);
                    $count++;
                }
            }
            $pdo->commit();
            echo "✅ Seeded {$count} widgets\n";
        }

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fatal("Setup error: " . $e->getMessage());
    }
    echo "</pre>";
    // Advance to step 2
    header('Location:?step=2');
    exit;
}

// STEP 2: redirect to verification
if ($step === '2') {
    header('Location:?step=3');
    exit;
}

// STEP 3: Verification and self-delete
if ($step === '3') {
    echo "<div class='card'><h2>Verification</h2><pre>";
    $tests = [
        'get_db()'          => fn() => get_db() instanceof PDO,
        'debug_log()'       => fn() => debug_log('Installer OK', [], 'INFO') === null,
        'login_user()'      => function() {
            $u = getenv('DEFAULT_ADMIN_USER');
            $p = getenv('DEFAULT_ADMIN_PASS');
            if (!login_user($u, $p)) {
                throw new Exception('Authentication failed');
            }
            logout_user();
            return true;
        },
        'get_user_widgets()'=> fn() => is_array(get_user_widgets()),
        'fetch_mps_api()'   => function() {
            $row = get_db()->query("SELECT endpoint FROM widgets LIMIT 1")->fetch();
            if (!$row) throw new Exception('No widgets found');
            parse_str(parse_url($row['endpoint'], PHP_URL_QUERY), $q);
            $data = fetch_mps_api($q['endpoint'], $q);
            if (!is_array($data)) throw new Exception('Bad API response');
            return true;
        },
    ];
    foreach ($tests as $label => $fn) {
        echo h($label) . ': ';
        try {
            echo $fn() === true ? "✅ PASS\n" : "❌ FAIL\n";
        } catch (Exception $e) {
            echo "❌ ERROR: " . h($e->getMessage()) . "\n";
        }
    }
    echo "</pre><p>Cleaning up…</p>";
    @unlink(__FILE__) 
        ? print("✅ Installer removed") 
        : print("⚠️ Could not delete installer");
    echo "</div>";
    echo "<script>setTimeout(()=>location='login.php',2000);</script>";
    exit;
}

// Should not reach here
fatal('Unknown installer step.');
