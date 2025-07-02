<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'helpers.php';

define('DASHBOARD_SETTINGS_FILE', __DIR__ . '/data/dashboard_settings.json');
require_once __DIR__ . '/src/php/DashboardManager.php';

$available_widgets = []; // Default fallback if logic fails
