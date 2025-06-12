<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Main Application Entry Point
 *
 * This file serves as the single entry point for the application,
 * handling routing and loading the appropriate views.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load configuration and utility functions
require_once 'includes/config.php'; // This should load constants from .env
require_once 'includes/constants.php'; // For other general constants if any
require_once 'includes/functions.php'; // <-- ENSURE THIS FILE IS IN includes/

// Basic routing logic
$current_view_slug = $_GET['view'] ?? 'dashboard';

// Define available views and their titles
$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports' => 'Reports & Analytics',
    // Add more views as needed
];

// Check if the requested view exists
if (!array_key_exists($current_view_slug, $available_views)) {
    // Redirect to dashboard or show a 404 error
    $current_view_slug = 'dashboard'; // Fallback to dashboard
    // header("HTTP/1.0 404 Not Found"); // Uncomment for actual 404
    // exit();
}

// Set selected customer ID from query parameter or session
$selected_customer_id = $_GET['customer_id'] ?? null;
if ($selected_customer_id) {
    $_SESSION['customer_id'] = $selected_customer_id;
} else if (!isset($_SESSION['customer_id'])) {
    $selected_customer_id = null;
} else {
    $selected_customer_id = $_SESSION['customer_id'];
}

// Include header
include_once 'includes/header.php';

// Include navigation
include_once 'includes/navigation.php';

// Render the current view
render_view('views/' . $current_view_slug . '.php', [
    'available_views' => $available_views,
    'current_view_slug' => $current_view_slug,
    'selected_customer_id' => $selected_customer_id,
]);

// Include footer
include_once 'includes/footer.php';
?>