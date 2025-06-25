<?php
declare(strict_types=1);

// ----------------------------------------
// DEBUG & ERROR HANDLING (Always at top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ----------------------------------------

// Load environment variables
require_once __DIR__ . '/includes/env.php';

// Define the view‐renderer helper
function render_view(string $viewPath): void {
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        http_response_code(500);
        echo "<h1>View not found: {$viewPath}</h1>";
        exit;
    }
}

// Output the page shell
require_once __DIR__ . '/includes/header.php';

// Inject the dashboard view
render_view(__DIR__ . '/views/dashboard.php');

// Close out with the footer
require_once __DIR__ . '/includes/footer.php';
?>
