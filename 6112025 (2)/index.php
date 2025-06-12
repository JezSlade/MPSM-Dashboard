<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * index.php
 *
 * Main entry for the MPSM Dashboard.
 *  
 * 1. Bootstrap config & helpers  
 * 2. Fetch data (customers, statuses)  
 * 3. Determine current view  
 * 4. Render <head> + CSS  
 * 5. include_partial() header  
 * 6. include_partial() view with all needed vars  
 * 7. include_partial() footer  
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// ---------------
// 1) Session & Data
// ---------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch customers (uses DEALER_CODE from .env)
$customers = fetch_customers();

// Handle customer dropdown POST
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$current_customer_code = $_SESSION['customer_code'] ?? null;

// Status indicators
$db_status  = ['status'=>'ok', 'message'=>'Database connected.'];
$api_status = ['status'=>'ok', 'message'=>'API reachable.'];

// Available views (tabs)
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics'
];

// Determine which view to show (fallback to dashboard)
$current_view = $_GET['view'] ?? 'dashboard';
if (! array_key_exists($current_view, $available_views)) {
    $current_view = 'dashboard';
}

// ---------------
// 2) Output HTML
// ---------------
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo sanitize_html(defined('APP_NAME') ? APP_NAME : 'MPSM Dashboard'); ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
</head>
<body class="theme-dark">

<?php
// ---------------
// 3) Render Header
// ---------------
include_partial('includes/header.php', [
    'db_status'           => $db_status,
    'api_status'          => $api_status,
    'customers'           => $customers,
    'current_customer_id' => $current_customer_code,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view,
]);

// ---------------
// 4) Render Main View
// ---------------
echo '<main class="dashboard-main">';

// Pass all needed variables into the view
$viewData = [
    'selected_customer_id' => $current_customer_code,
    'available_views'      => $available_views,
    'current_view_slug'    => $current_view
];

if (! include_partial("views/{$current_view}.php", $viewData)) {
    // Fallback if view file missing
    echo '<div class="view-not-found">';
    echo '<h2>View Not Found!</h2>';
    echo '<p>The requested view <code>' . sanitize_html($current_view) . '</code> could not be loaded.</p>';
    echo '<p>Check that <code>views/' . sanitize_html($current_view) . '.php</code> exists.</p>';
    echo '</div>';
}

echo '</main>';

// ---------------
// 5) Render Footer
// ---------------
include_partial('includes/footer.php');

?>
</body>
</html>
