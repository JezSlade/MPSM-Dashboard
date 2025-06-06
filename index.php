<?php
session_start(); // MUST BE THE VERY FIRST LINE

// Define SERVER_ROOT_PATH
defined('SERVER_ROOT_PATH') or define('SERVER_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once SERVER_ROOT_PATH . 'config.php';
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';
require_once SERVER_ROOT_PATH . 'auth.php';

$db = connect_db();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// User role and accessible modules
$user_id = $_SESSION['user_id'] ?? 'N/A';
$role = $_SESSION['role'] ?? 'Guest'; // Default to 'Guest' if not set

// Fetch role_id from the database based on the role name
$role_id = null;
if ($db) {
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $role_data = $result->fetch_assoc();
        if ($role_data) {
            $role_id = $role_data['id'];
        }
        $stmt->close();
    }
}

// Now call get_accessible_modules with $role_id and $user_id
if ($role_id !== null) {
    $accessible_modules = get_accessible_modules($role_id, $user_id);
} else {
    $accessible_modules = [];
}

// If *no* modules are accessible for the user at all, deny access immediately.
if (empty($accessible_modules)) {
    echo "<p class='text-red-500 p-4'>Access denied. No accessible modules found.</p>";
    session_unset();
    session_destroy();
    exit();
}

// Default module or requested module
$module = $_GET['module'] ?? 'dashboard';
// IMPORTANT FIX: Make the requested module name consistent with the keys in $accessible_modules (which are ucfirst'd)
$module = ucfirst($module);

// Check if the requested module is accessible to the user
if (!in_array($module, array_keys($accessible_modules))) {
    // The requested module is not accessible.
    if (in_array('Dashboard', array_keys($accessible_modules))) {
        $module = 'Dashboard'; // Set module to 'Dashboard' for consistency
        header("Location: index.php?module=Dashboard&error=Access+denied+to+requested+module.+Redirected+to+dashboard.");
        exit();
    } else {
        echo "<p class='text-red-500 p-4'>Access denied. You do not have permission to view this page or the Dashboard.</p>";
        session_unset();
        session_destroy();
        exit();
    }
}

// Construct the module path. Remember module filenames are typically lowercase (e.g., dashboard.php).
$module_path = SERVER_ROOT_PATH . 'modules/' . basename(strtolower($module)) . '.php';

// If the module file does not exist, and dashboard is accessible, default to dashboard.
// Otherwise, display an error.
if (!file_exists($module_path)) {
    if (in_array('Dashboard', array_keys($accessible_modules))) {
        $module = 'Dashboard';
        $module_path = SERVER_ROOT_PATH . 'modules/dashboard.php'; // Explicitly set to lowercase dashboard.php
        header("Location: index.php?module=Dashboard&error=Module+not+found.+Redirected+to+dashboard.");
        exit();
    } else {
        echo "<p class='text-red-500 p-4'>Error: Module file not found and Dashboard is not accessible.</p>";
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles-fallback.css">
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col">

        <main class="flex-1 p-6">
            <?php
            // Check for errors or success messages in URL parameters
            if (isset($_GET['error'])) {
                echo '<p class="text-red-500 mb-4 p-2 bg-red-900 bg-opacity-30 rounded">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            if (isset($_GET['success'])) {
                echo '<p class="text-green-500 mb-4 p-2 bg-green-900 bg-opacity-30 rounded">' . htmlspecialchars($_GET['success']) . '</p>';
            }

            // Dynamically include the module content
            include $module_path;
            ?>
        </main>

        <footer class="bg-gray-800 p-4 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> My PHP System. All rights reserved.
        </footer>
    </div>
</body>
</html>