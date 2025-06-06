<?php
// Define BASE_PATH if not already defined (e.g., if you move this to a shared config)
// --- IMPORTANT CHANGE: Adjusted BASE_PATH to '/mpsm/' based on your file location ---
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/mpsm/'); // Adjust this if your project is in a subfolder
}

// Define module properties: label and icon for sidebar menu items.
// Dashboard and Status are NOT listed here as they are fixed, non-selectable panels.
$all_modules = [
    'customers'     => ['label' => 'Customer Management', 'icon' => 'users'],
    'devices'       => ['label' => 'Device Inventory', 'icon' => 'device-mobile'],
    'permissions'   => ['label' => 'Permissions', 'icon' => 'shield-check'],
    'devtools'      => ['label' => 'DevTools', 'icon' => 'code'],
];

// Initialize role and accessible modules based on role
$role = $_POST['role'] ?? $_COOKIE['user_role'] ?? 'Guest'; // Get role from POST, then cookie, default to Guest
setcookie('user_role', $role, time() + (86400 * 30), "/"); // Set cookie for 30 days

$accessible_modules = [];
// Assign access to dynamic modules based on role
switch ($role) {
    case 'Developer':
        // Developer has access to all dynamic modules
        $accessible_modules = $all_modules;
        break;
    case 'Admin':
        // Admin has access to customers, devices, and permissions
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['customers', 'devices', 'permissions']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Service':
        // Service has access to devices
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['devices']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Sales':
        // Sales has access to customers
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['customers']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Guest':
    default:
        $accessible_modules = []; // Guests have no specific dynamic module access
        break;
}

// Determine current module to display in the 80% area
$current_module = $_GET['module'] ?? ''; // Default to empty string on initial load

// Validate if the requested module is accessible and should be loaded
if (!empty($current_module) && !array_key_exists($current_module, $accessible_modules)) {
    // If a module was requested but it's not accessible for the current role, clear it.
    $current_module = '';
}

// Define file paths for includes
$module_file = __DIR__ . '/modules/' . $current_module . '.php';
$dashboard_file = __DIR__ . '/modules/dashboard.php'; // Dashboard is always loaded in the top 20%
$status_file = __DIR__ . '/modules/status.php'; // Status is always loaded in the sidebar

// --- DEBUGGING OUTPUT START ---
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "\n";
// --- DEBUGGING OUTPUT END ---

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Control Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles.css">
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
        </div>
    </header>

    <div class="flex flex-1 mt-16">
        <aside class="glass w-64 p-4 fixed h-[calc(100vh-64px)] top-16 overflow-y-auto flex flex-col">
            <nav class="flex-1">
                <ul class="space-y-2">
                    <?php
                    // Icons array - using heroicons path data for simplicity
                    $icons = [
                        'users'        => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h2v-2a6 6 0 00-6-6H9a6 6 0 00-6 6v2H5m11-9a4 4 0 10-8 0 4 4 0 008 0z"></path></svg>',
                        'device-mobile'=> '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                        'shield-check' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.001 12.001 0 002 12c0 2.298.508 4.513 1.417 6.425C4.857 20.358 8.09 22 12 22s7.143-1.642 8.583-3.575C21.492 16.513 22 14.298 22 12c0-3.379-1.282-6.529-3.382-8.616z"></path></svg>',
                        'code'         => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>'
                    ];
                    ?>
                    <?php foreach ($accessible_modules as $module_key => $module_data): ?>
                        <li>
                            <a href="?module=<?php echo $module_key; ?>"
                               class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === $module_key ? 'active' : ''; ?>">
                                <?php echo $icons[$module_data['icon']] ?? ''; ?>
                                <span><?php echo $module_data['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="mt-auto">
                <?php
                if (file_exists($status_file)) {
                    include $status_file;
                } else {
                    echo '<div class="glass p-4 rounded-lg mt-4 text-sm text-center">';
                    echo '<h3 class="text-md text-yellow-neon mb-2">System Status Error</h3>';
                    echo '<p>Status module file not found: ' . htmlspecialchars($status_file) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </aside>

        <main class="glass flex-1 p-4 ml-64 flex flex-col">
            <div class="dashboard-static-20 glass p-4 mb-4">
                <?php
                // Always include dashboard.php in this top 20% area.
                if (file_exists($dashboard_file)) {
                    include $dashboard_file;
                } else {
                    echo '<h2 class="text-xl text-cyan-neon mb-4">Dashboard Not Found</h2>';
                    echo '<p>The dashboard file (' . htmlspecialchars($dashboard_file) . ') could not be found. Please ensure it exists.</p>';
                }
                ?>
            </div>

            <div class="module-area-80 relative flex-1 p-4">
                <?php
                if (!empty($current_module) && file_exists($module_file)) {
                    include $module_file; // Loads specific module content (e.g., customers, devices, permissions, devtools)
                } else {
                    // Display a message when no specific module is loaded in this area
                    echo '<p class="text-default">Select a module from the sidebar to view its content here.</p>';
                    // Optionally, if a module was requested but it\'s not accessible/found
                    if (!empty($_GET['module']) && !array_key_exists($_GET['module'], $all_modules)) {
                        echo '<p class="text-yellow-neon mt-2">The requested module "' . htmlspecialchars($_GET['module']) . '" does not exist.</p>';
                    } else if (!empty($_GET['module']) && !array_key_exists($_GET['module'], $accessible_modules)) {
                         echo '<p class="text-yellow-neon mt-2">The requested module "' . htmlspecialchars($_GET['module']) . '" is not accessible for your current role (' . htmlspecialchars($role) . ').</p>';
                    }
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement; // Target the <html> element for theme class

        // Function to set the theme and update the icon
        function setTheme(theme) {
            if (theme === 'dark') {
                htmlElement.classList.add('dark');
                document.getElementById('moon-icon').style.display = 'inline-block';
                document.getElementById('sun-icon').style.display = 'none';
            } else {
                htmlElement.classList.remove('dark');
                document.getElementById('moon-icon').style.display = 'none';
                document.getElementById('sun-icon').style.display = 'inline-block';
            }
            localStorage.setItem('theme', theme);
        }

        // --- Custom Theme Loading Logic ---
        function applyCustomTheme() {
            try {
                const customSettings = JSON.parse(localStorage.getItem('customThemeSettings'));
                if (customSettings) {
                    for (const [prop, value] of Object.entries(customSettings)) {
                        document.documentElement.style.setProperty(prop, value);
                    }
                }
            } catch (e) {
                console.error("Error parsing custom theme settings from localStorage:", e);
                localStorage.removeItem('customThemeSettings'); // Clear corrupted data
            }
        }

        // Get stored theme or default to system preference
        const storedTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const initialTheme = storedTheme || systemTheme; // Use stored, else system

        // Apply initial theme
        setTheme(initialTheme);
        // Apply custom theme settings AFTER the base theme is set
        applyCustomTheme();

        // Add event listener to toggle button
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
            // After changing the base theme, reapply custom settings in case they interact
            applyCustomTheme();
        });

        // Listen for system theme changes (if no stored preference)
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) { // Only update if no explicit user preference
                setTheme(e.matches ? 'dark' : 'light');
                applyCustomTheme(); // Also reapply customs
            }
        });
    </script>
</body>
</html>