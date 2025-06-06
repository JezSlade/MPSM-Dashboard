<?php
// index.php

// For debugging: display all errors (REMOVE OR COMMENT OUT IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

// Include configuration and core files
// IMPORTANT: Use __DIR__ for config.php as SERVER_ROOT_PATH is defined within it.
require_once __DIR__ . '/config.php'; // <-- FIXED THIS LINE

require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';
require_once SERVER_ROOT_PATH . 'auth.php';

// Initialize database connection early for setup check
global $db; // Ensure $db is accessible globally if connect_db doesn't set it automatically
if (!isset($db) || $db === null) {
    $db = connect_db();
}

// --- IMPORTANT: Conditional Setup Execution ---
// Only run setup.php if the 'roles' table (or another critical table) doesn't exist
// This prevents an infinite redirect loop after initial setup
if ($db && !table_exists($db, 'roles')) { // Check if 'roles' table exists
    require_once SERVER_ROOT_PATH . 'setup.php';
    // setup.php itself will handle the redirect after creation, so we just exit here
    exit; // Ensure script stops after setup.php redirects
}
// --- END Conditional Setup Execution ---


// --- Rest of your index.php logic (only runs if setup is complete) ---

// Check authentication
if (!isLoggedIn()) {
    header('Location: ' . WEB_ROOT_PATH . 'login.php');
    exit;
}

// Get user role and accessible modules
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'Guest'; // Default to Guest if no role set
$accessible_modules = [];

// Fetch role_id if user is logged in
$role_id = null;
if ($user_id) {
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $role_data = $result->fetch_assoc();
    if ($role_data) {
        $role_id = $role_data['id'];
        $accessible_modules = get_accessible_modules($role_id, $user_id);
    }
    $stmt->close();
}


// Determine which module to load
$module = $_GET['module'] ?? DEFAULT_MODULE; // Use DEFAULT_MODULE from config.php

// Ensure the requested module is accessible, or redirect to default
if (!array_key_exists(ucfirst($module), $accessible_modules)) {
    // If the requested module isn't accessible, try redirecting to the dashboard
    // If dashboard isn't accessible, this might lead to a loop.
    // A better approach for strict permissions might be to show an "Access Denied" page.
    if ($module !== DEFAULT_MODULE && array_key_exists(ucfirst(DEFAULT_MODULE), $accessible_modules)) {
        header('Location: ' . WEB_ROOT_PATH . 'index.php?module=' . DEFAULT_MODULE);
        exit;
    } else {
        // If even the default module isn't accessible, display access denied
        echo "<main class=\"glass p-6 rounded-lg shadow-neumorphic-light text-center\"><h1 class=\"text-4xl text-red-neon mb-4\">Access Denied</h1><p class=\"text-default\">You do not have permission to view this page or the default module.</p><p><a href=\"logout.php\" class=\"text-cyan-neon hover:underline\">Logout</a></p></main>";
        ob_end_flush();
        exit;
    }
}

$module_path = $accessible_modules[ucfirst($module)];

// Include the module content
include $module_path;

ob_end_flush();
?>