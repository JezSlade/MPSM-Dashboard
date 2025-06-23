<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/debug.php';   // ← NEW unified helper
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

// Resolve ?view=dashboard|sandbox
$requestedView = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'dashboard';
$requestedView = in_array($requestedView, ['dashboard', 'sandbox'], true)
    ? $requestedView
    : 'dashboard';

$viewFile = ($requestedView === 'sandbox')
    ? 'views/sandbox.php'
    : 'views/dashboard.php';

render_view($viewFile);

require_once __DIR__ . '/includes/footer.php';
