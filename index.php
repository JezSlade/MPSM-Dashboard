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

if (!array_key_exists($requested_module_pascal, $accessible_modules)) {
    // If the requested module is not accessible, try the default module
    $default_module_pascal = ucfirst(DEFAULT_MODULE);
    if (array_key_exists($default_module_pascal, $accessible_modules)) {
        $module = DEFAULT_MODULE; // Switch to default
        $requested_module_pascal = $default_module_pascal;
    } else {
        // Fallback: No accessible modules, or even default is not accessible.
        // Render a basic access denied page without the full layout.
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
            <div class="flex items-center justify-center min-h-screen">
                <main class="glass p-6 rounded-lg shadow-neumorphic-light text-center">
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

// Include the module content (this will be rendered inside the main HTML layout)
$module_path = $accessible_modules[$requested_module_pascal];

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
            <nav>
                <ul class="space-y-3">
                    <?php foreach ($accessible_modules as $mod_name => $mod_path): ?>
                        <li>
                            <a href="<?php echo WEB_ROOT_PATH; ?>index.php?module=<?php echo strtolower($mod_name); ?>"
                               class="menu-item <?php echo (strtolower($mod_name) === $module) ? 'active' : ''; ?>">
                                <?php echo $mod_name; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?php echo WEB_ROOT_PATH; ?>logout.php" class="menu-item logout-button">
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header glass p-4 mb-4 flex justify-between items-center">
                <h1 class="text-3xl text-cyan-neon"><?php echo ucfirst($module); ?></h1>
                <div class="user-info text-default">
                    Logged in as: <span class="font-semibold"><?php echo $_SESSION['username'] ?? 'Guest'; ?></span>
                    (Role: <span class="font-semibold"><?php echo $role; ?></span>)
                </div>
            </header>

            <section class="module-content">
                <?php
                // Include the actual module content here
                if (file_exists($module_path)) {
                    include $module_path;
                } else {
                    echo "<p class=\"text-red-500 p-4\">Error: Module file not found: " . htmlspecialchars($module_path) . "</p>";
                }
                ?>
            </section>
        </main>
    </div>

    </body>
</html>
<?php
// End output buffering and send content
ob_end_flush();
?>