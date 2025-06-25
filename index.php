<?php
declare(strict_types=1);

// ----------------------------------------
// DEBUG & ERROR HANDLING
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ----------------------------------------

// Load .env into getenv()/$_ENV
require_once __DIR__ . '/includes/env.php';

// Simple view-renderer helper
function render_view(string $viewPath): void {
    if (! file_exists($viewPath)) {
        http_response_code(500);
        echo "<h1>View not found: {$viewPath}</h1>";
        exit;
    }
    include $viewPath;
}

// Page shell
require_once __DIR__ . '/includes/header.php';

// Main content
render_view(__DIR__ . '/views/dashboard.php');

// Footer
require_once __DIR__ . '/includes/footer.php';
