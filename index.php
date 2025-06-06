<?php
// index.php

// Start output buffering early to prevent headers from being sent prematurely
ob_start();

// Start the session
session_start();

// Include the central configuration file FIRST to define paths
require_once __DIR__ . '/config.php';

// Include dependencies using the defined SERVER_ROOT_PATH
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';
include_once SERVER_ROOT_PATH . 'auth.php'; // Consider changing to require_once if auth is critical

// --- DEBUGGING INFO ---
// This will output comments in your HTML source, viewable via "Inspect Element" or "View Source"
echo "\n";
echo "\n";
echo "\n";
echo "\n";
// --- END DEBUGGING INFO ---


// Initialize database connection
// Ensure $db is globally accessible if functions require it globally
$db = connect_db();
if (!$db) {
    // Log the error and display a user-friendly message
    error_log("Failed to connect to database in index.php");
    die("<h1>Service Unavailable</h1><p>The application is currently unable to connect to the database. Please try again later.</p>");
}


// Check if setup needs to run
// This helps ensure the database schema is in place
if (!table_exists($db, 'users') || !table_exists($db, 'roles') || !table_exists($db, 'permissions')) {
    header('Location: ' . WEB_ROOT_PATH . 'setup.php');
    exit;
}

// User authentication and role management
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user role and permissions
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$user_permissions = get_user_permissions($user_id);

// Determine accessible modules based on role and permissions
// Define a mapping of roles to default accessible modules
$role_module_map = [
    'Developer' => ['dashboard', 'customers', 'devices', 'permissions', 'devtools', 'status'],
    'Admin' => ['dashboard', 'customers', 'devices', 'permissions', 'status'],
    'Service' => ['dashboard', 'devices', 'status'],
    'Sales' => ['dashboard', 'customers', 'status'],
    'Guest' => ['dashboard', 'status']
];

// Get default modules for the current role
$accessible_modules = $role_module_map[$role] ?? ['dashboard', 'status'];

// Filter modules based on explicit 'view_' permissions
$final_accessible_modules = [];
foreach ($accessible_modules as $module_name) {
    if (has_permission('view_' . $module_name, $user_permissions)) {
        $final_accessible_modules[] = $module_name;
    }
}
$accessible_modules = array_unique($final_accessible_modules); // Remove any duplicates


// Module Routing
$module = $_GET['module'] ?? 'dashboard'; // Default module is dashboard

// Validate module and check permissions
if (!in_array($module, $accessible_modules) || !has_permission('view_' . $module, $user_permissions)) {
    $module = 'dashboard'; // Fallback to dashboard if module is inaccessible
}

$module_path = SERVER_ROOT_PATH . 'modules/' . $module . '.php';

// Prepare header data for the current module
$header_data = [
    'title' => ucwords(str_replace('_', ' ', $module)), // e.g., "Dashboard", "Dev Tools"
    'username' => $_SESSION['username'],
    'role' => $role,
    'accessible_modules' => $accessible_modules,
    'WEB_ROOT_PATH' => WEB_ROOT_PATH, // Pass to header.php for links
    'logout_url' => WEB_ROOT_PATH . 'logout.php'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM - <?php echo $header_data['title']; ?></title>
    <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles.css">
    <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles-fallback.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="dark">
    <?php require_once SERVER_ROOT_PATH . 'includes/header.php'; // Pass data via included file's scope ?>

    <main class="glass p-6 rounded-lg shadow-outer-dark mt-4">
        <?php
        if (file_exists($module_path)) {
            require_once $module_path;
        } else {
            echo "<p class='text-red-500'>Module not found: " . htmlspecialchars($module) . "</p>";
        }
        ?>
    </main>

    <?php require_once SERVER_ROOT_PATH . 'includes/footer.php'; ?>
</body>
</html>
<?php
ob_end_flush(); // End output buffering and send content to browser
?>