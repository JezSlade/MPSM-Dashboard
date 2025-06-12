<?php
declare(strict_types=1);
/**
 * index.php – main dashboard entry point with bootstrap, data fetch, and rendering.
 *
 * Patches applied:
 *  1. Inline error display for debugging.
 *  2. Wrapped bootstrap in try/catch to expose fatal errors.
 *  3. Safe includes of config.php and functions.php.
 *  4. Inherited error-reporting settings from config.php.
 *  5. Passing all variables required by views (including available_views & current_view_slug).
 *  6. Correct JS reference to js/script.js.
 *  7. Unified glassmorphic debug panel + toggle.
 */

// ─── 0) Enable inline error display ──────────────────────────────────────────
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ─── 1) Bootstrap config & helpers ──────────────────────────────────────────
try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/functions.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>Fatal error during bootstrap:' . "\n"
       . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
       . '</pre>';
    error_log('Bootstrap error in index.php: ' . $e->getMessage());
    exit;
}

// ─── 2) Apply inherited error-reporting based on DEBUG_MODE ───────────────────
ini_set('display_errors',        DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors',DEBUG_MODE ? '1' : '0');
error_reporting(E_ALL);

// ─── 3) Fetch data and handle inputs ────────────────────────────────────────
$customers = fetch_customers();
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}

// ─── 4) API status (example) ────────────────────────────────────────────────
$api_status = [
    'status'  => 'ok',
    'message' => 'API reachable.',
];

// ─── 5) Views setup ─────────────────────────────────────────────────────────
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics',
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
// ─── 6) Header ─────────────────────────────────────────────────────────────
include_partial('includes/header.php', [
    'app_name'            => APP_NAME,
    'customers'           => $customers,
    'current_customer_id' => $_SESSION['customer_code'] ?? null,
    'api_status'          => $api_status,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view,
]);
?>

<?php
// ─── 7) CardEditor ─────────────────────────────────────────────────────────
$cardEditorPath = __DIR__ . '/includes/CardEditor.php';
if (file_exists($cardEditorPath)) {
    require_once $cardEditorPath;
    (new CardEditor())->render();
} else {
    debug_log('Missing CardEditor include: ' . $cardEditorPath, 'ERROR');
    echo '<p>Error loading CardEditor component.</p>';
}
?>

<?php if (DEBUG_MODE && DEBUG_PANEL_ENABLED): ?>
  <!-- ─── 8) Debug Toggle & Panel ────────────────────────────────────────── -->
  <button id="debug-toggle" title="Toggle Debug Panel">🐞</button>
  <div id="debug-panel" class="hidden">
    <h4>Debug Log (<?php echo date('Y-m-d'); ?>)</h4>
    <pre><?php
      $logfile = __DIR__ . '/logs/debug-' . date('Y-m-d') . '.log';
      if (file_exists($logfile)) {
          echo sanitize_html(file_get_contents($logfile));
      } else {
          echo 'No log file found for today.';
      }
    ?></pre>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const btn   = document.getElementById('debug-toggle');
      const panel = document.getElementById('debug-panel');
      btn.addEventListener('click', () => {
        panel.classList.toggle('hidden');
      });
    });
  </script>
<?php endif; ?>

<?php
// ─── 9) Main View ─────────────────────────────────────────────────────────
include_partial("views/{$current_view}.php", [
    'customers'           => $customers,
    'current_customer_id' => $_SESSION['customer_code'] ?? null,
    'api_status'          => $api_status,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view,
]);
?>

<?php
// ─── 10) Footer ───────────────────────────────────────────────────────────
include_partial('includes/footer.php');
?>

</body>
</html>
