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
require_once __DIR__ . '/config.php';

// Now use SERVER_ROOT_PATH for other includes
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';
require_once SERVER_ROOT_PATH . 'auth.php';

// Initialize database connection
global $db;
if (!isset($db) || $db === null) {
    $db = connect_db();
}

// --- Conditional Setup Execution ---
if (!$db || !table_exists($db, 'users') || !table_exists($db, 'roles') || !table_exists($db, 'modules')) {
    error_log("Database tables not found or DB connection failed. Redirecting to setup.php.");
    header('Location: ' . WEB_ROOT_PATH . 'setup.php');
    exit;
}
// --- END Conditional Setup Execution ---

// --- Authentication Check ---
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
        // get_accessible_modules will now correctly consider 'manage_permissions' for 'permissions' module
        $accessible_modules = get_accessible_modules($role_id, $user_id);
    }
    $stmt->close();
} else {
    $accessible_modules = []; // No user ID, no accessible modules
}

// Cache user permissions in session for quicker checks later
if (isset($_SESSION['user_id'])) {
    $_SESSION['user_permissions'] = get_user_permissions($_SESSION['user_id']);
} else {
    $_SESSION['user_permissions'] = []; // No permissions if not logged in
}

// --- Module Routing ---
$module = $_GET['module'] ?? DEFAULT_MODULE;
$requested_module_pascal = ucfirst($module);

// If the requested module is not accessible (excluding dashboard, which is always visible),
// or if it's the dashboard itself (which we handle separately now)
if (!array_key_exists($requested_module_pascal, $accessible_modules) || strtolower($requested_module_pascal) === 'dashboard') {
    // If the requested module is not accessible, default to DEFAULT_MODULE
    // but only if DEFAULT_MODULE is actually accessible.
    $default_module_pascal = ucfirst(DEFAULT_MODULE);
    if (strtolower($requested_module_pascal) !== DEFAULT_MODULE && array_key_exists($default_module_pascal, $accessible_modules)) {
         $module = DEFAULT_MODULE;
         $requested_module_pascal = $default_module_pascal;
    } else if (strtolower($requested_module_pascal) !== DEFAULT_MODULE && !array_key_exists($default_module_pascal, $accessible_modules)) {
        // Fallback: Default module also not accessible. Render access denied.
        ob_end_clean(); // Clear any partial output
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied</title>
            <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles.css">
            <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles-fallback.css">
        </head>
        <body class="dark-mode">
            <div class="access-denied-container">
                <main class="glass p-6 rounded-lg shadow-neumorphic-dark text-center">
                    <h1 class="text-4xl text-red-neon mb-4">Access Denied</h1>
                    <p class="text-default">You do not have permission to view this module or any accessible modules.</p>
                    <p><a href="<?php echo WEB_ROOT_PATH; ?>logout.php" class="text-cyan-neon hover:underline">Logout</a></p>
                </main>
            </div>
        </body>
        </html>
        <?php
        ob_end_flush();
        exit;
    }
}

// Get path for the *requested* module (if it's not dashboard, which is handled separately)
$module_path = null;
if (strtolower($module) !== 'dashboard') {
    $module_path = $accessible_modules[$requested_module_pascal] ?? null;
}

// --- HTML Structure Starts Here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM - <?php echo ucfirst($module); ?></title>
    <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles.css">
    <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles-fallback.css">
    <link rel="icon" href="<?php echo WEB_ROOT_PATH; ?>favicon.ico" type="image/x-icon">
</head>
<body class="dark-mode">
    <div class="dashboard-layout">
        <aside class="sidebar glass p-4">
            <h2 class="text-2xl text-yellow-neon mb-6">MPSM</h2>
            <nav class="flex-grow"> <ul class="space-y-3">
                    <?php
                    foreach ($accessible_modules as $mod_name => $mod_path):
                        // Skip dashboard menu item as it's always visible
                        if (strtolower($mod_name) === 'dashboard') continue;
                        // Skip status menu item as it's fixed at the bottom
                        if (strtolower($mod_name) === 'status') continue;
                    ?>
                        <li>
                            <a href="<?php echo WEB_ROOT_PATH; ?>index.php?module=<?php echo strtolower($mod_name); ?>"
                               class="menu-item <?php echo (strtolower($mod_name) === $module) ? 'active' : ''; ?>">
                                <?php echo $mod_name; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="sidebar-status-container mt-auto"> <?php include SERVER_ROOT_PATH . 'modules/status.php'; ?>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-header glass p-4 mb-4 flex justify-between items-center">
                <h1 class="text-3xl text-cyan-neon"><?php echo ucfirst($module); ?></h1>
                <div class="header-right-controls flex items-center space-x-4">
                    <button id="theme-toggle" class="glass theme-toggle-button p-2 rounded-full shadow-neumorphic-dark">
                        <svg class="w-6 h-6 text-default" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h1M3 12H2m8.005-9.005l.707-.707M15.364 18.364l-.707.707M3.636 3.636l.707-.707m16.728 16.728l-.707-.707M6.343 17.657l-.707.707M17.657 6.343l.707-.707"></path>
                        </svg>
                    </button>
                    <div class="user-info text-default">
                        Logged in as: <span class="font-semibold"><?php echo $_SESSION['username'] ?? 'Guest'; ?></span>
                        (Role: <span class="font-semibold"><?php echo $role; ?></span>)
                    </div>
                </div>
            </header>

            <section class="dashboard-fixed-section p-4 mb-4 glass rounded-lg shadow-neumorphic-dark">
                <?php include SERVER_ROOT_PATH . 'modules/dashboard.php'; ?>
            </section>

            <section class="other-modules-content flex-grow p-4 glass rounded-lg shadow-neumorphic-dark">
                <?php
                if (strtolower($module) !== 'dashboard') {
                    if ($module_path && file_exists($module_path)) {
                        include $module_path;
                    } else {
                        // This case should ideally be caught earlier by the routing logic,
                        // but serves as a final fallback.
                        echo "<p class=\"text-red-500\">Error: Requested module '" . htmlspecialchars($module) . "' not found or not accessible.</p>";
                    }
                } else {
                    echo "<p class=\"text-default\">Select a module from the sidebar to view its content.</p>";
                }
                ?>
            </section>
        </main>
    </div>

    <script>
        // Light Mode Toggle JavaScript
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            const body = document.body;

            // Load saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                body.classList.add(savedTheme);
            } else {
                // Default to dark mode if no preference is saved
                body.classList.add('dark-mode');
            }

            themeToggleBtn.addEventListener('click', () => {
                if (body.classList.contains('dark-mode')) {
                    body.classList.remove('dark-mode');
                    localStorage.setItem('theme', ''); // Store empty string for light mode
                } else {
                    body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark-mode');
                }
            });

            // Adjust sidebar height to push status to bottom
            const sidebar = document.querySelector('.sidebar');
            const nav = sidebar.querySelector('nav');
            const statusContainer = sidebar.querySelector('.sidebar-status-container');

            // Set sidebar to flex column
            sidebar.style.display = 'flex';
            sidebar.style.flexDirection = 'column';

            // Ensure nav takes available space
            nav.style.flexGrow = '1';

            // Ensure status container is pushed to bottom
            statusContainer.style.marginTop = 'auto';
        });
    </script>
</body>
</html>
<?php
// End output buffering and send content
ob_end_flush();
?>