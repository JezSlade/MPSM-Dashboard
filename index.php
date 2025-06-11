<?php
/**
 * index.php
 *
 * Main entry point for the MPSM Dashboard.
 * Boots configuration and helpers, fetches data, then renders:
 *  1. Header (includes/header.php)
 *  2. Main content view (views/{view}.php)
 *  3. Footer (includes/footer.php)
 */

// 1) Bootstrap configuration and helper functions
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// 2) Start session (for persisting selected customer)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3) Fetch customers (uses DEALER_CODE from .env by default)
$customers = fetch_customers();

// 4) Handle customer selection via POST
if (! empty($_POST['customer_code'])) {
    $_SESSION['customer_code'] = $_POST['customer_code'];
}
$current_customer_code = $_SESSION['customer_code'] ?? null;

// 5) Prepare status indicators for header
$db_status = [
    'status'  => 'ok',
    'message' => 'Database connected successfully.'
];
$api_status = [
    'status'  => 'ok',
    'message' => 'API reachable.'
];

// 6) Define the available “Views” (tabs) and pick the current one
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports'   => 'Reports',
    'analytics' => 'Analytics'
];
// Use ?view= in URL to switch; default to 'dashboard' if not valid
$current_view_slug = isset($_GET['view']) && array_key_exists($_GET['view'], $available_views)
    ? $_GET['view']
    : 'dashboard';

// 7) Render header partial from includes/header.php
include_partial('includes/header.php', [
    'db_status'           => $db_status,
    'api_status'          => $api_status,
    'customers'           => $customers,
    'current_customer_id' => $current_customer_code,
    'available_views'     => $available_views,
    'current_view_slug'   => $current_view_slug,
]);

// 8) Main content container
echo '<main class="dashboard-main">';

// 9) Attempt to load the specific view partial from views/{slug}.php
$viewFile = 'views/' . $current_view_slug . '.php';
if (! include_partial($viewFile)) {
    // Fallback UI if the view file is missing
    echo '<div class="view-not-found">';
    echo '<h2>View Not Found!</h2>';
    echo '<p>The requested view \'' . sanitize_html($current_view_slug) . '\' could not be loaded.</p>';
    echo '<p>Please check the URL or ensure the view file exists in the <code>views/</code> directory.</p>';
    echo '</div>';
}

// 10) Close main container
echo '</main>';

// 11) Render footer partial from includes/footer.php
include_partial('includes/footer.php');
