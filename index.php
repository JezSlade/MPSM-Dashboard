<?php
declare(strict_types=1);
/**
 * index.php – main dashboard entry point with bootstrap, data fetch, and rendering.
 *
 * Now with exactly one unified debug toggle + panel.
 */

// 0) Always show errors in dev
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// 1) Bootstrap
try {
    require_once __DIR__.'/config.php';
    require_once __DIR__.'/functions.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>Fatal bootstrap error:'."\n"
       .htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8')
       .'</pre>';
    exit;
}

// 2) Respect DEBUG_MODE
ini_set('display_errors', DEBUG_MODE?'1':'0');
ini_set('display_startup_errors', DEBUG_MODE?'1':'0');
error_reporting(E_ALL);

// 3) Fetch data & handle inputs
$customers = fetch_customers();
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}

// 4) API status example
$api_status = ['status'=>'ok','message'=>'API reachable.'];

// 5) Views setup
$available_views = [
    'dashboard'=>'Dashboard Overview',
    'reports'=>'Reports',
    'analytics'=>'Analytics',
];
$current_view = $_GET['view'] ?? 'dashboard';
if (! isset($available_views[$current_view])) {
    $current_view = 'dashboard';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo sanitize_html(APP_NAME); ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="js/script.js" defer></script>
</head>
<body>

<?php
// Header
include_partial('includes/header.php', [
    'app_name'=>APP_NAME,
    'customers'=>$customers,
    'current_customer_id'=>$_SESSION['customer_code']??null,
    'api_status'=>$api_status,
    'available_views'=>$available_views,
    'current_view_slug'=>$current_view,
]);
?>

<?php
// CardEditor
$editor = __DIR__.'/includes/CardEditor.php';
if (file_exists($editor)) {
    require_once $editor;
    (new CardEditor())->render();
} else {
    debug_log("Missing CardEditor: $editor",'ERROR');
    echo '<p>Error loading CardEditor.</p>';
}
?>

<?php if (DEBUG_MODE && DEBUG_PANEL_ENABLED): ?>
  <!-- Unified Debug Toggle + Panel -->
  <button id="debug-toggle" title="Toggle Debug Panel">🐞</button>
  <div id="debug-panel" class="hidden">
    <div class="debug-header">
      <h3>Debug Log (<?php echo date('Y-m-d'); ?>)</h3>
      <button class="debug-button" onclick="document.getElementById('debug-panel').classList.add('hidden')">−</button>
    </div>
    <div class="debug-content">
      <pre class="debug-log-output"><?php
        $log = __DIR__ . '/logs/debug-'.date('Y-m-d').'.log';
        echo file_exists($log)
          ? sanitize_html(file_get_contents($log))
          : 'No log for today.';
      ?></pre>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      const btn = document.getElementById('debug-toggle'),
            panel = document.getElementById('debug-panel');
      btn.addEventListener('click',()=>panel.classList.toggle('hidden'));
    });
  </script>
<?php endif; ?>

<?php
// Main view
include_partial("views/{$current_view}.php",[
    'customers'=>$customers,
    'current_customer_id'=>$_SESSION['customer_code']??null,
    'api_status'=>$api_status,
    'available_views'=>$available_views,
    'current_view_slug'=>$current_view,
]);
?>

<?php
// Footer (no debug here)
include_partial('includes/footer.php');
?>

</body>
</html>
