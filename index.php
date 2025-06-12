<?php
declare(strict_types=1);
/**
 * index.php – main dashboard entry point with bootstrap, data fetch, and rendering.
 *
 * Patches applied:
 *  1. Safe bootstrap: require config.php and functions.php first.
 *  2. Inherited error-reporting from config.php.
 *  3. Safe CardEditor include with file_exists check.
 */

// ─── 1) Bootstrap config and helpers ────────────────────────────────────────
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// ─── 2) Error reporting settings (inherited) ───────────────────────────────
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors', DEBUG_MODE ? '1' : '0');
error_reporting(E_ALL);


/**
 * index.php
 *
 * 1. Bootstrap (config.php loads .env, session, constants)
 * 2. Helpers
 * 3. Fetch data
 * 4. Determine view
 * 5. Render <head> + CSS
 * 6. Render header
 * 7. Render CardEditor
 * 8. Render debug panel (if DEBUG_MODE)
 * 9. Render main view
 * 10. Render footer
 */

 // ─── Fetch & handle inputs ──────────────────────────────────────────────────
$customers = fetch_customers();

// Handle customer selection POST
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}

// Determine API status
$api_status = [
    'status'  => 'ok',
    'message' => 'API reachable.',
];

// Available views
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics',
];

// Current view slug
$current_view = $_GET['view'] ?? 'dashboard';
if (! array_key_exists($current_view, $available_views)) {
    $current_view = 'dashboard';
}

// ─── Render HTML head and CSS ───────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo sanitize_html(APP_NAME); ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="js/main.js" defer></script>
</head>
<body>

<?php
// ─── Render header partial ──────────────────────────────────────────────────
include_partial('views/header.php', [
    'app_name' => APP_NAME,
    'customers' => $customers,
    'current_customer_id' => $_SESSION['customer_code'] ?? null,
    'api_status' => $api_status,
    'available_views' => $available_views,
    'current_view_slug' => $current_view,
]);
?>

<?php
// ─── Card Editor ────────────────────────────────────────────────────────────
$cardEditorPath = __DIR__ . '/includes/CardEditor.php';
if (file_exists($cardEditorPath)) {
    require_once $cardEditorPath;
    (new CardEditor())->render();
} else {
    debug_log('Missing CardEditor include: ' . $cardEditorPath, 'ERROR');
    echo '<p>Error loading CardEditor component.</p>';
}
?>

<?php if (DEBUG_MODE): ?>
  <!-- ─── Debug Panel ──────────────────────────────────────────────────────── -->
  <div id="debug-panel" class="hidden">
    <h4>🐞 Debug Log (<?php echo date('Y-m-d'); ?>)</h4>
    <pre><?php
      $logfile = __DIR__ . '/logs/debug-' . date('Y-m-d') . '.log';
      if (file_exists($logfile)) {
          echo sanitize_html(file_get_contents($logfile));
      } else {
          echo "No log file found for today.";
      }
    ?></pre>
  </div>
<?php endif; ?>

<?php
// ─── Render main view template ─────────────────────────────────────────────
include_partial("views/{$current_view}.php", [
    'customers'           => $customers,
    'current_customer_id' => $_SESSION['customer_code'] ?? null,
    'api_status'          => $api_status,
]);

// ─── Render footer partial ──────────────────────────────────────────────────
include_partial('views/footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn   = document.getElementById('debug-toggle');
  const panel = document.getElementById('debug-panel');
  if (btn && panel) {
    btn.addEventListener('click', () => {
      panel.classList.toggle('hidden');
    });
  }
});
</script>

</body>
</html>
