<?php
/**
 * index.php
 *
 * Main entry point for the MPSM Dashboard.
 * Boots configuration, helper functions, then renders:
 *  1. <head> + CSS link
 *  2. Header (includes/header.php)
 *  3. Main content view (views/{view}.php)
 *  4. Footer (includes/footer.php)
 */

// 1) Bootstrap config and helpers
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// 2) Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3) Fetch customers
$customers = fetch_customers();

// 4) Handle customer selection
if (!empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$current_customer_code = $_SESSION['customer_code'] ?? null;

// 5) Status indicators for header
$db_status  = ['status'=>'ok','message'=>'DB connected.'];
$api_status = ['status'=>'ok','message'=>'API reachable.'];

// 6) Define Views (tabs)
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics'
];
$current_view_slug = isset($_GET['view']) && array_key_exists($_GET['view'], $available_views)
    ? $_GET['view']
    : 'dashboard';

// 7) Output HTML head
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo sanitize_html(APP_NAME); ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
</head>
<body class="theme-dark">
<?php

// 8) Render header
include_partial('includes/header.php', [
    'db_status'           => $db_status,
    'api_status'          => $api_status,
    'customers'           => $customers,
    'current_customer_id' => $current_customer_code,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view_slug,
]);

// 9) Main container
echo '<main class="dashboard-main">';

// 10) Load view from views/{slug}.php
$viewFile = 'views/' . $current_view_slug . '.php';
if (! include_partial($viewFile)) {
    echo '<div class="view-not-found">';
    echo '<h2>View Not Found!</h2>';
    echo '<p>The requested view \'' . sanitize_html($current_view_slug) . '\' could not be loaded.</p>';
    echo '<p>Check the URL or ensure <code>views/' . sanitize_html($current_view_slug) . '.php</code> exists.</p>';
    echo '</div>';
}

echo '</main>';

// 11) Render footer
include_partial('includes/footer.php');

?>
</body>
</html>
