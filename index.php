<?php
/**
 * index.php
 *
 * Main entry for MPSM Dashboard
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (session_status()===PHP_SESSION_NONE) session_start();

// Fetch customers
$customers = fetch_customers();

// Handle selection
if (!empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$selected_customer_code = $_SESSION['customer_code'] ?? null;

// Status data
$db_status  = ['status'=>'ok','message'=>'DB connected'];
$api_status = ['status'=>'ok','message'=>'API reachable'];

// Views
$available_views = [
    'dashboard'=>'Dashboard Overview',
    'reports'=>'Reports',
    'analytics'=>'Analytics'
];
$current_view = $_GET['view'] ?? 'dashboard';
if (!isset($available_views[$current_view])) {
    $current_view = 'dashboard';
}

// HTML output
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
// Header
include_partial('includes/header.php',[
    'db_status'=>$db_status,
    'api_status'=>$api_status,
    'customers'=>$customers,
    'current_customer_id'=>$selected_customer_code,
    'available_views'=>$available_views,
    'current_view_slug'=>$current_view
]);

echo '<main class="dashboard-main">';

// View
if (! include_partial("views/{$current_view}.php",[
    'selected_customer_id'=>$selected_customer_code
])) {
    echo '<div class="view-not-found"><h2>View Not Found!</h2><p>Ensure views/'.$current_view.'.php exists.</p></div>';
}

echo '</main>';

// Footer
include_partial('includes/footer.php');
?>

</body>
</html>
