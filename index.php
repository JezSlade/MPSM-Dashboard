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
    echo "<p style='color: yellow;'>DEBUG: Not logged in. Redirecting to login.php</p>"; // DEBUG
    header('Location: login.php');
    exit;
}

// User role and accessible modules
$user_id = $_SESSION['user_id'] ?? 'N/A';
$role = $_SESSION['role'] ?? 'Guest'; // Default to 'Guest' if not set
echo "<p style='color: yellow;'>DEBUG: User ID: " . htmlspecialchars($user_id) . ", Session Role: " . htmlspecialchars($role) . "</p>"; // DEBUG

// Fetch role_id from the database based on the role name
$role_id = null;
if ($db) { // Ensure $db is connected before querying
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $role_data = $result->fetch_assoc();
        if ($role_data) {
            $role_id = $role_data['id'];
            echo "<p style='color: yellow;'>DEBUG: Fetched Role ID: " . htmlspecialchars($role_id) . " for role: " . htmlspecialchars($role) . "</p>"; // DEBUG
        } else {
            echo "<p style='color: yellow;'>DEBUG: Role '" . htmlspecialchars($role) . "' not found in database for user_id: " . htmlspecialchars($user_id) . "</p>"; // DEBUG
        }
        $stmt->close();
    } else {
        echo "<p style='color: yellow;'>DEBUG: Failed to prepare statement for fetching role_id: " . htmlspecialchars($db->error) . "</p>"; // DEBUG
    }
} else {
    echo "<p style='color: yellow;'>DEBUG: Database connection is null when trying to fetch role_id in index.php.</p>"; // DEBUG
}

// Now call get_accessible_modules with $role_id and $user_id
if ($role_id !== null) {
    $accessible_modules = get_accessible_modules($role_id, $user_id);
    echo "<p style='color: yellow;'>DEBUG: Accessible modules array after get_accessible_modules call: <pre>" . htmlspecialchars(print_r($accessible_modules, true)) . "</pre></p>"; // DEBUG
} else {
    // Fallback if role_id cannot be determined (e.g., if role doesn't exist or DB error)
    echo "<p style='color: yellow;'>DEBUG: Role ID is null, accessible modules set to empty array for user " . htmlspecialchars($user_id) . " with role: " . htmlspecialchars($role) . ".</p>"; // DEBUG
    $accessible_modules = [];
}

// Default module or requested module
$module = $_GET['module'] ?? 'dashboard';

// Check if the requested module is accessible to the user
if (!in_array($module, array_keys($accessible_modules))) { // Use array_keys for module names
    // If the requested module is not accessible,
    // and they can access the dashboard, redirect to dashboard.
    // Otherwise, deny access.
    if (in_array('dashboard', array_keys($accessible_modules))) { // Use array_keys here too
        $module = 'dashboard';
        echo "<p style='color: yellow;'>DEBUG: Access denied to requested module. Redirecting to dashboard.</p>"; // DEBUG
        header("Location: index.php?module=dashboard&error=Access+denied+to+requested+module.+Redirected+to+dashboard.");
        exit();
    } else {
        // If no modules are accessible at all, or only inaccessible ones are requested.
        // This might happen for a new user with no permissions, or a misconfiguration.
        echo "<p style='color: yellow;'>DEBUG: Final state: No accessible modules found or requested module inaccessible. Accessible modules array: <pre>" . htmlspecialchars(print_r($accessible_modules, true)) . "</pre></p>"; // DEBUG
        echo "<p class='text-red-500 p-4'>Access denied. No accessible modules found.</p>";
        session_unset();
        session_destroy();
        exit();
    }
}

$module_path = SERVER_ROOT_PATH . 'modules/' . basename($module) . '.php';

// If the module file does not exist, and dashboard is accessible, default to dashboard.
// Otherwise, display an error.
if (!file_exists($module_path)) {
    if (in_array('dashboard', array_keys($accessible_modules))) { // Use array_keys here too
        $module = 'dashboard';
        $module_path = SERVER_ROOT_PATH . 'modules/dashboard.php';
        echo "<p style='color: yellow;'>DEBUG: Module not found. Redirecting to dashboard.</p>"; // DEBUG
        header("Location: index.php?module=dashboard&error=Module+not+found.+Redirected+to+dashboard.");
        exit();
    } else {
        echo "<p class='text-red-500 p-4'>Error: Module file not found and dashboard is not accessible.</p>";
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