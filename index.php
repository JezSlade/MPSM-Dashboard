<?php declare(strict_types=1);
// /index.php — root entry point

// 0) Ensure debug.log exists
$logFile = __DIR__ . '/logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

// 1) Load global config
require_once __DIR__ . '/includes/config.php';

// 2) Header
require_once __DIR__ . '/includes/header.php';

// 3) Navigation (with error-catch)
try {
    require_once __DIR__ . '/includes/navigation.php';
} catch (\Throwable $e) {
    error_log("Navigation error: " . $e->getMessage());
    echo "<p class='error'>Failed to load navigation.</p>";
}

// 4) Main view
render_view(__DIR__ . '/views/dashboard.php');

// 5) Footer
require_once __DIR__ . '/includes/footer.php';
