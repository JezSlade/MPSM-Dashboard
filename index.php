<?php
// index.php
// ────────────────────────────────────────────────────────────────────────────────
// Enable PHP error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Define SERVER_ROOT_PATH for server-side file includes
// This will be the absolute path to the directory containing index.php
define('SERVER_ROOT_PATH', __DIR__ . '/');

// Define WEB_ROOT_PATH for browser-facing URLs (e.g., for CSS, JS, image links)
// This should be the URL path to your project's root on the web server
// Adjust '/mpsm/' if your project is directly in your domain root (e.g., '/')
define('WEB_ROOT_PATH', '/mpsm/'); 

// --- DEBUGGING INFO ---
// This will output comments in your HTML source, viewable via "Inspect Element" or "View Source"
echo "\n";
echo "\n";
echo "\n";
echo "\n";
// --- END DEBUGGING INFO ---


// Include dependencies
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';
include_once SERVER_ROOT_PATH . 'auth.php'; // Consider changing to require_once if auth is critical

// Check if setup is needed - modified to always check tables
$setup_needed = false;
$required_tables = ['modules', 'users', 'roles', 'permissions', 'role_permissions', 'user_roles', 'user_permissions'];
foreach ($required_tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $setup_needed = true;
        break;
    }
}

if ($setup_needed || isset($_GET['reset'])) {
    require_once SERVER_ROOT_PATH . 'setup.php';
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Set default session data for testing
if (!isset($_SESSION['user_id'])) {
    $result = $db->query("SELECT id FROM users WHERE username = 'admin'");
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user ? $user['id'] : 1;
    $_SESSION['role'] = 'Admin';
    $_SESSION['username'] = 'admin';
    // Match status.php expectation
}

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $_SESSION['role'] = $_POST['role'];
    if (isset($_SESSION['user_id'])) {
        $_SESSION['permissions'] = get_user_permissions($_SESSION['user_id']);
        error_log("Permissions for user_id " . $_SESSION['user_id'] . ": " . json_encode($_SESSION['permissions']));
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$role = $_SESSION['role'] ?? 'Guest';
if (isset($_SESSION['user_id'])) {
    $_SESSION['permissions'] = get_user_permissions($_SESSION['user_id']);
    error_log("Initial permissions for user_id " . $_SESSION['user_id'] . ": " . json_encode($_SESSION['permissions']));
}

$modules = [
    'customers'   => ['label' => 'Customers',   'icon' => 'users',         'permission' => 'view_customers'],
    'devices'     => ['label' => 'Devices',     'icon' => 'device-mobile', 'permission' => 'view_devices'],
    'permissions' => ['label' => 'Permissions', 'icon' => 'lock-closed',   'permission' => 'manage_permissions'],
    'devtools'    => ['label' => 'DevTools',    'icon' => 'wrench',        'permission' => 'view_devtools']
];
$accessible_modules = [];
foreach ($modules as $module => $key) {
    if (has_permission($key['permission'])) {
        $accessible_modules[$module] = $key;
    }
}

$current_module  = isset($_GET['module']) && isset($accessible_modules[$_GET['module']]) ? $_GET['module'] : null;

// Paths for actual file inclusion
$dashboard_file  = SERVER_ROOT_PATH . 'modules/dashboard.php';
$status_file     = SERVER_ROOT_PATH . 'modules/status.php'; // Explicitly define for include below
$module_to_include = $current_module ? SERVER_ROOT_PATH . "modules/{$current_module}.php" : null;

// --- DEBUGGING INFO (File Existence Checks) ---
echo "\n";
echo "\n";
echo "\n";
echo "\n";
if ($current_module) {
    echo "\n";
    echo "\n";
}
// --- END DEBUGGING INFO ---

if (!$db) {
    error_log("Database connection is null.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Control Panel</title>
    <link rel="stylesheet" href="<?php echo WEB_ROOT_PATH; ?>styles.css"> 
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Adjust Tailwind config to use CSS variables for dynamic colors
        tailwind.config = {
            darkMode: 'class', // Enable dark mode based on 'dark' class on HTML or Body
            theme: {
                extend: {
                    colors: {
                        // Use CSS variables for the main theme colors
                        'bg-primary':    'var(--bg-primary)',
                        'text-default':  'var(--text-default)',
                        'bg-glass':      'var(--bg-glass)',
                        'shadow-outer-dark': 'var(--shadow-outer-dark)',
                        'shadow-outer-light': 'var(--shadow-outer-light)',
                        'shadow-inset-dark': 'var(--shadow-inset-dark)',
                        'shadow-inset-light': 'var(--shadow-inset-light)',
                        'neon-cyan':     'var(--neon-cyan)',
                        'neon-magenta':  'var(--neon-magenta)',
                        'neon-yellow':   'var(--neon-yellow)',
                        'menu-item-bg-start': 'var(--menu-item-bg-start)',
                        'menu-item-bg-end': 'var(--menu-item-bg-end)',
                        'menu-item-active-bg-start': 'var(--menu-item-active-bg-start)',
                        'menu-item-active-bg-end': 'var(--menu-item-active-bg-end)',
                        'panel-shadow-outer': 'var(--panel-shadow-outer)',
                        'panel-shadow-inset': 'var(--panel-shadow-inset)',
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen font-sans flex flex-col">
    <header class="glass p-4 fixed w-full top-0 z-10 h-16 flex justify-between items-center">
        <h1 class="text-2xl text-cyan-neon">MPSM Control Panel</h1>
        <div class="flex items-center space-x-4">
            <button id="theme-toggle">
                <svg id="moon-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg id="sun-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h1M4 12H3m15.354 5.354l-.707.707M6.346 6.346l-.707-.707m12.728 0l-.707-.707M6.346 17.654l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </button>
            <form method="POST" action="" class="inline">
                <select name="role" onchange="this.form.submit()" class="bg-bg-primary text-text-default p-2 rounded glass-inner-shadow">
                    <?php foreach (['Developer', 'Admin', 'Service', 'Sales', 'Guest'] as $r): ?>
                        <option value="<?php echo $r; ?>" <?php echo $role === $r ? 'selected' : ''; ?>>
                            <?php echo $r; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="logout.php" class="text-neon-magenta">Logout</a>
            <a href="?reset" class="text-neon-yellow">Reset Setup</a>
        </div>
    </header>

    <div class="flex flex-1 mt-16">
        <aside class="glass w-64 p-4 fixed h-[calc(100vh-64px)] top-16 overflow-y-auto flex flex-col">
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li>
                        <a href="?module=dashboard"
                           class="flex items-center p-2 rounded-lg menu-item <?php echo ($current_module === 'dashboard' || !$current_module) ? 'active text-neon-yellow' : ''; ?>">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-9v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="?module=status"
                           class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === 'status' ? 'active text-neon-yellow' : ''; ?>">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-4m3 4v-6m3 4v-2m3.5-3.5a9 9 0 11-16.1 0 9 9 0 0116.1 0z"></path></svg>
                            <span>Status</span>
                        </a>
                    </li>

                    <?php foreach ($modules as $module_key => $details): ?>
                        <?php if (has_permission($details['permission'])): ?>
                            <li>
                                <a href="?module=<?php echo $module_key; ?>"
                                   class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === $module_key ? 'active text-neon-yellow' : ''; ?>">
                                    <?php
                                        // Simplified icons array - you'll likely want to put this in functions.php or similar
                                        $icons = [
                                            'customers'   => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h2v-2a6 6 0 00-6-6H9a6 6 0 00-6 6v2H5m11-9a4 4 0 10-8 0 4 4 0 008 0z"></path></svg>',
                                            'devices'     => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                                            'permissions' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.001 12.001 0 002 12c0 2.298.508 4.513 1.417 6.425C4.857 20.358 8.09 22 12 22s7.143-1.642 8.583-3.575C21.492 16.513 22 14.298 22 12c0-3.379-1.282-6.529-3.382-8.616z"></path></svg>',
                                            'devtools'    => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m6.364-12.364l-1.414 1.414M6.05 18.05l-1.414 1.414m-1.414-1.414l1.414-1.414m12.728 0l1.414 1.414M12 12a4 4 0 110-8 4 4 0 010 8z"></path></svg>'
                                        ];
                                        echo $icons[$details['icon']] ?? '';
                                    ?>
                                    <span><?php echo $details['label']; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="mt-auto">
                <?php
                // Include the Status module directly here for the footer of the sidebar
                if (file_exists($status_file)) {
                    include $status_file;
                } else {
                    echo "";
                }
                ?>
            </div>
        </aside>

        <main class="glass flex-1 p-4 ml-64 flex flex-col">
            <div class="dashboard-static-20 glass p-4 mb-4">
                <h2 class="text-xl text-neon-cyan mb-4">MPSM Overview</h2>
                <p>This is a static summary section for key dashboard information. It occupies 20% of the available vertical space in the main content area.</p>
                <?php
                    // You can include a separate file for static overview here if needed
                ?>
            </div>

            <div class="module-area-80 relative flex-1 p-4">
                <?php
                // This section loads the dynamic module content or the default dashboard
                if ($current_module && file_exists($module_to_include)) {
                    include $module_to_include; // Loads specific module content (e.g., customers, devices, devtools)
                } else if (file_exists($dashboard_file)) {
                    // Default to the dashboard content if no valid module is selected
                    include $dashboard_file;
                } else {
                    echo '<p class="text-neon-yellow">Welcome to the MPSM Control Panel. Select a module from the sidebar, or view the default dashboard.</p>';
                    echo "";
                }
                ?>
            </div>
        </main>
    </div>
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            sunIcon.style.display = 'none';
            moonIcon.style.display = 'block';
        } else {
            document.documentElement.classList.remove('dark');
            sunIcon.style.display = 'block';
            moonIcon.style.display = 'none';
        }

        themeToggle.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        });
    </script>
    <?php echo "\n"; ?>
</body>
</html>