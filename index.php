<?php
// index.php
// ────────────────────────────────────────────────────────────────────────────────
// Enable PHP error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Define BASE_PATH
define('BASE_PATH', __DIR__ . '/');

// Include dependencies
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';
include_once BASE_PATH . 'auth.php';

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
    require_once BASE_PATH . 'setup.php';
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
$dashboard_file  = BASE_PATH . 'modules/dashboard.php';
$module_file     = $current_module ? BASE_PATH . "modules/{$current_module}.php" : null;
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
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles-fallback.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'teal-custom': '#00cec9',
                        'cyan-neon':   '#00FFFF',
                        'magenta-neon':'#FF00FF',
                        'yellow-neon': '#FFFF00',
                        'black-smoke': '#1C2526'
                    }
                }
            }
        };
    </script>
    <style>
        /* ── Original “.glass”, “.menu-item”, and “.floating-module” rules ───────── */
        .glass {
            background: rgba(28, 37, 38, 0.8);
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.7), inset 0 0 15px rgba(0,255,255,0.4);
        }
        .menu-item {
            background: linear-gradient(145deg, rgba(255,255,255,0.1), rgba(255,255,255,0.03));
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .menu-item.active {
            background: linear-gradient(145deg, rgba(255,255,0,0.3), rgba(255,255,0,0.15));
        }

        /* ── ADJUSTED: `.floating-module` for proper "floating" within the main area ── */
        .floating-module {
            position: absolute; /* Keeps it positioned relative to its parent (main.glass) */
            top: 1rem; /* Aligns with the 1rem padding of main.glass */
            left: 1rem; /* Aligns with the 1rem padding of main.glass */
            right: 1rem; /* Aligns with the 1rem padding of main.glass */
            bottom: 1rem; /* Aligns with the 1rem padding of main.glass */
            z-index: 20; /* Ensures it's above other dashboard content */
            background: rgba(28,37,38,0.9);
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.7), inset 0 0 15px rgba(255,255,0,0.2);
            padding: 1.5rem; /* Internal padding for the module's content */
            overflow-y: auto;
            box-sizing: border-box; /* Crucial for including padding in total size */
        }

        @supports not (backdrop-filter: blur(10px)) {
            .glass {
                background: rgba(28,37,38,1);
            }
        }

        /* ── ADDED: `position: relative;` to `main.glass` for `absolute` children ──── */
        main.glass {
            position: relative; /* Makes main a positioned parent for absolute children like .floating-module */
            /* No other explicit positioning here, relies on flexbox for layout */
            /* Tailwind's p-4 provides padding and flex-1 handles sizing */
            overflow-y: auto; /* Allows content within main to scroll */
            overflow-x: hidden;
            box-sizing: border-box; /* Ensures padding is included in the element's total width and height */
        }
    </style>
</head>
<body class="bg-black-smoke text-white min-h-screen font-sans">
    <header class="glass p-4 fixed w-full top-0 z-10">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl text-cyan-neon">MPSM Control Panel</h1>
            <div>
                <form method="POST" action="" class="inline">
                    <select name="role" onchange="this.form.submit()" class="bg-black-smoke text-white p-2 rounded">
                        <?php foreach (['Developer', 'Admin', 'Service', 'Sales', 'Guest'] as $r): ?>
                            <option value="<?php echo $r; ?>" <?php echo $role === $r ? 'selected' : ''; ?>>
                                <?php echo $r; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="logout.php" class="ml-4 text-magenta-neon">Logout</a>
                <a href="?reset"    class="ml-4 text-yellow-neon">Reset Setup</a>
            </div>
        </div>
    </header>

    <div class="flex mt-16">
        <aside class="glass w-64 p-4 fixed h-[calc(100vh-80px)] top-16 overflow-y-auto flex flex-col">
            <nav class="flex-1">
                <ul class="space-y-2">
                    <?php foreach ($accessible_modules as $module => $key): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>"
                               class="flex items-center p-2 text-gray-300 rounded-lg menu-item <?php echo $current_module === $module ? 'active text-yellow-neon' : ''; ?>">
                                <?php
                                    $icons = [
                                        'users'        => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5a4 4 0 110 5.4M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.2M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
                                        'device-mobile'=> '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 01-2-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                                        'lock-closed'  => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1 3.5-1 6.8-2.8 9.5m-3.4-2l.1-.1A14 14 0 008 11a4 4 0 118 0c0 1-.1 2-.2 3m-2.1 6.8A22 22 0 0015 17m3.8 1.1c.7-2.2 1-4.7 1-7A8 8 0 008 4M3 15.4c.6-1.3 1-2.8 2-4.4m1 3.4a3 3 0 013-3m0 3.4a3 3 0 00-3 3m3-3v6m-1.5-1.5a1.5 1.5 0 113 0m-3 0a1.5 1.5 0 00-1.5-1.5m1.5 4.5v-3m0 3h-3"></path></svg>',
                                        'wrench'       => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.3 4.3c.4-1.8 2.9-1.8 3.4 0a1.7 1.7 0 002.6 1.1c1.5-2.3 3-.8 2.4 2.4a1.7 1.7 0 001 2.5c1.8.4 1.8 2.9 0 3.4a1.7 1.7 0 00-1.1 2.6c-.9 1.5-.8 3.4-2.4 2.4a1.7 1.7 0 00-2.6 1c-.4 1.8-2.9 1.8-3.4 0a1.7 1.7 0 00-2.6-1c-1.5.9-3.3-.8-2.4-2.4-1-1-2.6 0-2 1 0c-1.4 1.8 1.9 2.4-2.3-.9-.5-2.3 0-2.6-1.1z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'
                                    ];
                                echo $icons[$key['icon']] ?? '';
                                ?>
                                <span><?php echo $key['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="mt-auto">
                <?php include_once BASE_PATH . 'modules/status.php'; ?>
            </div>
        </aside>

        <main class="glass flex-1 p-4">
            <?php
            if ($current_module && file_exists($module_file)) {
                include $module_file;
            } elseif (file_exists($dashboard_file)) {
                include $dashboard_file;
            } else {
                echo '<p class="text-yellow-neon">Welcome to the MPSM Control Panel. Select a module from the sidebar.</p>';
            }
            ?>
        </main>
    </div>
</body>
</html>