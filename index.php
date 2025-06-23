<?php
declare(strict_types=1);  // must precede all other statements

// ------------------------------------------------------------------
// DEBUG BLOCK (Always Keep at Top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ------------------------------------------------------------------

// 0) Bootstrap & layout
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

// 1) Resolve requested view via ?view=dashboard|sandbox
$requestedView = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'dashboard';
$allowedViews  = ['dashboard', 'sandbox'];
if (!in_array($requestedView, $allowedViews, true)) {
    $requestedView = 'dashboard'; // safe fallback
}

$viewFile = ($requestedView === 'sandbox')
    ? 'views/sandbox.php'
    : 'views/dashboard.php';

// 2) Render the chosen view
render_view($viewFile);

// 3) Footer
require_once __DIR__ . '/includes/footer.php';
