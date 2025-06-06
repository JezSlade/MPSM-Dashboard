<?php
// index.php

// Ensure config.php is the first thing included to set up paths and basic services
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

// Debug: Check if session is started and user is logged in
error_log("index.php: Script started.");
error_log("index.php: Session status: " . session_status());
error_log("index.php: User logged in (before redirect check): " . (isLoggedIn() ? 'Yes' : 'No'));

// Redirect to login if not logged in
if (!isLoggedIn()) {
    error_log("index.php: User not logged in, redirecting to login.php");
    header('Location: login.php');
    exit;
}

// Get user role and permissions
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'Guest'; // Default role if not set
error_log("index.php: User ID: " . ($user_id ?? 'N/A') . ", Role: " . $role);

// Fetch user permissions for the current session if not already set
if ($user_id && !isset($_SESSION['user_permissions'])) {
    if ($db) { // Ensure DB connection exists before trying to fetch permissions
        $_SESSION['user_permissions'] = get_user_permissions($user_id);
        error_log("index.php: Fetched user permissions during page load (fallback).");
    } else {
        error_log("index.php: DB connection not available, cannot fetch user permissions.");
    }
}

// Get user's role_id from the database to pass to get_accessible_modules
$role_id = null;
if ($db && $role) {
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
    } else {
        error_log("index.php: Failed to prepare role ID statement: " . $db->error);
    }
}
error_log("index.php: Role ID for '$role': " . ($role_id ?? 'N/A'));

// Get accessible modules based on current user's role and user ID
$accessible_modules = [];
if ($role_id !== null && $db) { // Ensure DB connection before calling get_accessible_modules
    $accessible_modules = get_accessible_modules($role_id, $user_id);
    error_log("index.php: Accessible modules count: " . count($accessible_modules));
    error_log("index.php: Accessible modules: " . print_r(array_keys($accessible_modules), true));
} else {
    error_log("index.php: Cannot get accessible modules, role_id is null or DB not connected.");
}

// Determine which module to load
$current_module = DEFAULT_MODULE; // Default to 'dashboard'
if (isset($_GET['module'])) {
    $requested_module = htmlspecialchars($_GET['module']);
    // Check if the requested module is in the list of accessible modules
    // Use strtolower for key lookup to match the actual file name, but keys in $accessible_modules are ucfirst
    $module_found = false;
    foreach ($accessible_modules as $name => $path) {
        if (strtolower($name) === strtolower($requested_module)) {
            $current_module = strtolower($name); // Ensure the module name used is lowercase for consistency
            $module_found = true;
            error_log("index.php: Requested module '$requested_module' is accessible, loading '$current_module'.");
            break;
        }
    }
    if (!$module_found && strtolower($requested_module) !== DEFAULT_MODULE) {
        error_log("index.php: Requested module '$requested_module' not accessible or not found, defaulting to dashboard.");
        $current_module = DEFAULT_MODULE; // Ensure it defaults back if not accessible
    }
} else {
    error_log("index.php: No module requested, loading default '$current_module'.");
}


// Theme management
$theme = $_COOKIE['theme'] ?? 'dark'; // Default theme
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'] === 'light' ? 'light' : 'dark';
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // 30-day cookie
}
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles-fallback.css">
</head>
<body class="bg-primary text-default font-sans antialiased min-h-screen flex flex-col transition-colors duration-300">

    <style id="custom-theme-styles">
        /* This will be populated by devtools.php JavaScript */
        /* Default colors are set in styles.css and are theme-dependent */
    </style>

    <div class="main-container flex flex-grow">
        <aside class="sidebar glass p-4 shadow-neumorphic-dark flex flex-col justify-between">
            <div>
                <h2 class="text-3xl text-cyan-neon font-bold mb-6 text-center">MPSM</h2>
                <nav>
                    <ul>
                        <?php foreach ($accessible_modules as $mod_name => $mod_path): ?>
                            <li class="mb-2">
                                <a href="?module=<?php echo strtolower($mod_name); ?>"
                                   class="menu-item flex items-center p-3 rounded-lg transition-all duration-200 <?php echo strtolower($mod_name) === $current_module ? 'active' : ''; ?>">
                                    <span class="ml-3"><?php echo $mod_name; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="mb-2">
                             <a href="?theme=<?php echo $theme === 'dark' ? 'light' : 'dark'; ?>"
                               class="menu-item flex items-center p-3 rounded-lg transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <?php if ($theme === 'dark'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.325-6.675l-.707-.707M6.675 18.325l-.707-.707M18.325 18.325l-.707-.707M6.675 6.675l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z"></path>
                                    <?php else: ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                    <?php endif; ?>
                                </svg>
                                <span class="ml-3">Switch to <?php echo $theme === 'dark' ? 'Light' : 'Dark'; ?> Mode</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="mt-8">
                <a href="logout.php" class="menu-item flex items-center p-3 rounded-lg w-full justify-center transition-all duration-200">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8 overflow-auto">
            <header class="mb-6 flex items-center justify-between">
                <h1 class="text-4xl text-yellow-neon font-bold">
                    <?php
                    // Display current module title, default to Dashboard
                    echo ucfirst($current_module);
                    ?>
                </h1>
                <div class="text-right text-sm text-default">
                    Logged in as: <span class="text-cyan-neon font-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span><br>
                    Role: <span class="text-cyan-neon font-semibold"><?php echo htmlspecialchars($_SESSION['role'] ?? 'N/A'); ?></span>
                </div>
            </header>

            <section class="module-content-area glass p-4 mb-4 rounded-lg shadow-neumorphic-dark">
                <?php
                $module_path = SERVER_ROOT_PATH . $current_module . '.php'; // Corrected path to module files
                error_log("index.php: Attempting to include module: " . $module_path);

                if (file_exists($module_path)) {
                    // Check if the user has permission to view this specific module
                    $has_view_permission = has_permission('view_' . $current_module);

                    // Special case for 'permissions' module, it requires 'manage_permissions'
                    if ($current_module === 'permissions') {
                        if (has_permission('manage_permissions')) {
                            include $module_path;
                            error_log("index.php: Included permissions module.");
                        } else {
                            echo "<p class='text-red-500 p-4'>You do not have permission to manage permissions.</p>";
                            error_log("index.php: Access denied to permissions module for user (manage_permissions missing).");
                        }
                    } else if ($has_view_permission || has_permission('custom_access')) { // 'custom_access' is a general override
                        include $module_path;
                        error_log("index.php: Included module '$current_module'.");
                    } else {
                        echo "<p class='text-red-500 p-4'>You do not have permission to view this module.</p>";
                        error_log("index.php: Access denied to '$current_module' for user (view_ permission missing).");
                    }
                } else {
                    echo "<p class='text-red-500 p-4'>Module file not found: " . htmlspecialchars($current_module) . ".php</p>";
                    error_log("index.php: Module file not found at expected path: " . $module_path);
                }
                ?>
            </section>

        </main>
    </div>

    <script>
        // JavaScript for dynamic theme settings from devtools.php
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const customThemeSettings = JSON.parse(localStorage.getItem('customThemeSettings') || '{}');

            // Apply saved custom settings
            for (const [prop, value] of Object.entries(customThemeSettings)) {
                root.style.setProperty(prop, value);
            }
        });
    </script>
</body>
</html>