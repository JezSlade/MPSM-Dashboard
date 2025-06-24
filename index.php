<?php
// index.php
// Entrypoint: pulls in header (UI only), then dashboard (with its own <head>).
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// 1) Render the HTML head + open <body> + logo/theme (no CSS here)
require_once __DIR__ . '/includes/header.php';

// 2) Render the main dashboard view (which includes its own <head> block)
include __DIR__ . '/views/dashboard.php';

// 3) Render the closing footer
require_once __DIR__ . '/includes/footer.php';
