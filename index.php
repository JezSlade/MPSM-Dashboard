<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
// ini_set('error_log', __DIR__ . '/../logs/debug.log'); // Adjust path as needed
// ----------------------------------------

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
?>
