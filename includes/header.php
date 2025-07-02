<?php
// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// --- Cache Control Headers ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
// --- End Cache Control Headers ---

session_start();

// Include configuration and helper functions/classes
require_once 'config.php';
require_once 'helpers.php';
require_once 'src/php/DashboardManager.php';
require_once 'src/php/FileManager.php'; // Ensure FileManager is included if used for widget creation

// Instantiate DashboardManager
// $available_widgets is populated by discover_widgets() in config.php
$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, $available_widgets);

// Load current dashboard state (settings + widget states)
$current_dashboard_state = $dashboardManager->loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'widgets_state'

// IMPORTANT: Initialize $_SESSION['dashboard_settings'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['dashboard_settings'] = $current_dashboard_state;

// The index.php no longer handles POST requests directly for actions.
// All actions are now handled by dedicated API endpoints via AJAX.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This part should ideally not be reached for action_type POSTs
    // as AJAX requests are now handled by api/dashboard.php etc.
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by AJAX and then reloaded via loadDashboardState().
$settings = array_replace_recursive($dashboardManager->loadDashboardState(), $_SESSION['dashboard_settings'] ?? []);

// Pass available widgets to the view
global $available_widgets; // Ensure $available_widgets from config.php is accessible

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <!-- Chart.js CDN for charting widgets -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <style>
        :root {
            --accent: <?= $settings['accent_color'] ?>;
            --glass-bg: rgba(35, 40, 49, <?= $settings['glass_intensity'] ?>);
            --blur-amount: <?= $settings['blur_amount'] ?>;
        }
        .widget {
            transition: <?= $settings['enable_animations'] ? 'var(--transition)' : 'none' ?>;
        }
        .widget:hover {
            <?php if ($settings['enable_animations']): ?>
            transform: translateY(-5px);
            box-shadow:
                12px 12px 24px var(--shadow-dark),
                -12px -12px 24px rgba(74, 78, 94, 0.1);
            <?php endif; ?>
        }
    </style>
</head>
<body>
