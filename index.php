<?php
// index.php

// Start output buffering and session at the very beginning
ob_start();
session_start();

// For debugging: display all errors (REMOVE OR COMMENT OUT IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// IMPORTANT: config.php must be included first to define SERVER_ROOT_PATH and WEB_ROOT_PATH
// Use __DIR__ for config.php as SERVER_ROOT_PATH is defined within it.
require_once __DIR__ . '/config.php';

// Now use SERVER_ROOT_PATH for other includes
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php'; // get_accessible_modules() and table_exists() are here
require_once SERVER_ROOT_PATH . 'auth.php';     // isLoggedIn(), logout() are here

// Initialize database connection
global $db;
if (!isset($db) || $db === null) {
    $db = connect_db();
}

// --- Conditional Setup Execution ---
// This block ensures that setup.php is run only once if tables are not found.
// The table_exists() function should be defined in db.php or functions.php.
if (!$db || !table_exists($db, 'users') || !table_exists($db, 'roles') || !table_exists($db, 'modules')) {
    error_log("Database tables not found. Redirecting to setup.php.");
    header('Location: ' . WEB_ROOT_PATH . 'setup.php');
    exit;
}
// --- END Conditional Setup Execution ---

// --- Authentication Check ---
// Redirect to login page if user is not logged in
if (!isLoggedIn()) {
    header('Location: ' . WEB_ROOT_PATH . 'login.php');
    exit;
}

// --- User and Permissions Initialization ---
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'Guest'; // Default to Guest if no role set
$accessible_modules = [];

// Fetch role_id and accessible modules if user is logged in
$role_id = null;
if ($user_id) {
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $role_data = $result->fetch_assoc();
    if ($role_data) {
        $role_id = $role_data['id'];
        // Get accessible modules for the logged-in user
        $accessible_modules = get_accessible_modules($role_id, $user_id);
    }
    $stmt->close();
} else {
    // If no user_id, ensure accessible_modules is empty
    $accessible_modules = [];
}

// Cache user permissions in session for quicker checks later
if (isset($_SESSION['user_id'])) {
    $_SESSION['user_permissions'] = get_user_permissions($_SESSION['user_id']);
} else {
    $_SESSION['user_permissions'] = []; // No permissions if not logged in
}

// --- Module Routing ---
// Determine which module to load based on GET parameter, default to dashboard
$module = $_GET['module'] ?? DEFAULT_MODULE; // DEFAULT_MODULE should be defined in config.php

// Ensure the requested module is accessible by the current user's role/permissions
// Convert module name to PascalCase for array_key_exists check, as module names in $accessible_modules are ucfirst()
$requested_module_pascal = ucfirst($module);

if (!array_key_exists($requested_module_pascal, $accessible_modules)) {
    // If the requested module is not accessible, check if the default module is accessible
    $default_module_pascal = ucfirst(DEFAULT_MODULE);
    if (array_key_exists($default_module_pascal, $accessible_modules)) {
        // Redirect to the default accessible module
        header('Location: ' . WEB_ROOT_PATH . 'index.php?module=' . DEFAULT_MODULE);
        exit;
    } else {
        // If even the default module isn't accessible, show an access denied message
        // This scenario means a user with no permissions somehow logged in, or no modules are active/assigned
        echo "<main class=\"glass p-6 rounded-lg shadow-neumorphic-light text-center\"><h1 class=\"text-4xl text-red-neon mb-4\">Access Denied</h1><p class=\"text-default\">You do not have permission to view any modules or the default module.</p><p><a href=\"logout.php\" class=\"text-cyan-neon hover:underline\">Logout</a></p></main>";
        ob_end_flush();
        exit;
    }
}

// Include the module content
$module_path = $accessible_modules[$requested_module_pascal];
if (file_exists($module_path)) {
    include $module_path;
} else {
    // Fallback if the file path is somehow invalid (should not happen with correct setup)
    echo "<main class=\"glass p-6 rounded-lg shadow-neumorphic-light text-center\"><h1 class=\"text-4xl text-red-neon mb-4\">Error</h1><p class=\"text-default\">Module file not found: " . htmlspecialchars($module_path) . "</p></main>";
}

// End output buffering and send content
ob_end_flush();
?>