<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/**
 * index.php
 *
 * Main entry for the MPSM Dashboard.
 *  
 * 1. Bootstrap config & helpers  
 * 2. Fetch data (customers, statuses)  
 * 3. Determine current view & customer  
 * 4. Render <head> + CSS  
 * 5. Render header, main view, footer
 */

// 1) Load config, autoloaders, helpers
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/functions.php';

// 2) Fetch data
$customers = fetch_customers();

// 3a) Determine view
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics'
];
$current_view = $_GET['view'] ?? 'dashboard';
if (! array_key_exists($current_view, $available_views)) {
    $current_view = 'dashboard';
}

// 3b) Handle customer selection via GET or POST
if (! empty($_GET['customer_code'])) {
    $_SESSION['customer_code'] = $_GET['customer_code'];
} elseif (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$current_customer_code = $_SESSION['customer_code'] ?? null;

// 4) Status indicators
$db_status  = ['status'=>'ok', 'message'=>'Database connected.'];
$api_status = ['status'=>'ok', 'message'=>'API reachable.'];

// 5) Render page
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo sanitize_html(defined('APP_NAME') ? APP_NAME : 'MPSM Dashboard'); ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
</head>
<body class="theme-dark">
<?php
include_partial('includes/header.php', [
    'db_status'           => $db_status,
    'api_status'          => $api_status,
    'customers'           => $customers,
    'current_customer_id' => $current_customer_code,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view
]);
echo '<main class="dashboard-main">';
include_partial("views/{$current_view}.php", [
    'selected_customer_id' => $current_customer_code,
    'available_views'      => $available_views,
    'current_view_slug'    => $current_view
]);
echo '</main>';
include_partial('includes/footer.php');
?>
<script src="<?php echo BASE_URL; ?>js/script.js"></script>
</body>
</html>
