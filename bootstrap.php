<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'helpers.php';

// Load env vars, db, config, etc. as needed
define('DASHBOARD_SETTINGS_FILE', __DIR__ . '/data/dashboard_settings.json');

// Corrected path references for production context
require_once __DIR__ . '/src/php/DashboardManager.php';
require_once __DIR__ . '/src/php/DashboardStateManager.php';

$available_widgets = []; // Default fallback if logic fails
