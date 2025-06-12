<?php
declare(strict_types=1);
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

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Fetch customers
$customers = fetch_customers();

// Handle customer selection POST
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$current_customer_code = $_SESSION['customer_code'] ?? null;

// Status indicators
$db_status  = ['status'=>'ok','message'=>'Database connected.'];
$api_status = ['status'=>'ok','message'=>'API reachable.'];

// Views
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics',
];
$current_view = $_GET['view'] ?? 'dashboard';
if (! array_key_exists($current_view, $available_views)) {
    $current_view = 'dashboard';
}

// ---------------
// HTML OUTPUT
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo sanitize_html(APP_NAME); ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/debug.css">
</head>
<body class="theme-dark">

<?php
// ---------------
// Header
include_partial('includes/header.php', [
    'db_status'           => $db_status,
    'api_status'          => $api_status,
    'customers'           => $customers,
    'current_customer_id' => $current_customer_code,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view,
]);

// ---------------
// Card Editor
require_once __DIR__ . '/includes/CardEditor.php';
(new CardEditor())->render();

// ---------------
// Debug Panel
if (DEBUG_MODE): ?>
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
// ---------------
// Main View
echo '<main class="dashboard-main">';
$viewData = [
    'selected_customer_id' => $current_customer_code,
    'available_views'      => $available_views,
    'current_view_slug'    => $current_view,
];
if (! include_partial("views/{$current_view}.php", $viewData)) {
    echo '<div class="view-not-found">';
    echo '<h2>View Not Found!</h2>';
    echo '<p>Could not load view <code>' . sanitize_html($current_view) . '</code>.</p>';
    echo '</div>';
}
echo '</main>';

// ---------------
// Footer
include_partial('includes/footer.php');
?>

<!-- ---------------
// Debug Toggle Script
--------------- -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  const btn   = document.getElementById('debug-toggle');
  const panel = document.getElementById('debug-panel');
  if (btn && panel) {
    btn.addEventListener('click', function(){
      panel.classList.toggle('hidden');
    });
  }
});
</script>

</body>
</html>
