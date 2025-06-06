<?php
// Define BASE_PATH if not already defined (e.g., if you move this to a shared config)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/'); // Adjust this if your project is in a subfolder
}

// Assume basic module definitions for demonstration.
// In a real application, these might come from a database or more complex configuration.
$all_modules = [
    'dashboard'     => ['label' => 'Dashboard', 'icon' => 'home'],
    'customers'     => ['label' => 'Customer Management', 'icon' => 'users'],
    'devices'       => ['label' => 'Device Inventory', 'icon' => 'device-mobile'],
    'access_control'=> ['label' => 'Access Control', 'icon' => 'lock-closed'],
    'system_settings'=>['label' => 'System Settings', 'icon' => 'wrench'],
    'permissions'   => ['label' => 'Permissions', 'icon' => 'shield-check'], // Added back Permissions module
    'devtools'      => ['label' => 'DevTools', 'icon' => 'code'],           // Added back DevTools module
];

// Initialize role and accessible modules based on role
$role = $_POST['role'] ?? $_COOKIE['user_role'] ?? 'Guest'; // Get role from POST, then cookie, default to Guest
setcookie('user_role', $role, time() + (86400 * 30), "/"); // Set cookie for 30 days

$accessible_modules = [];
switch ($role) {
    case 'Developer':
        $accessible_modules = $all_modules; // All modules
        break;
    case 'Admin':
        // Ensure Admin has access to all previously available and newly added modules
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['dashboard', 'customers', 'devices', 'access_control', 'system_settings', 'permissions', 'devtools']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Service':
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['dashboard', 'devices']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Sales':
        $accessible_modules = array_filter($all_modules, fn($k) => in_array($k, ['dashboard', 'customers']), ARRAY_FILTER_USE_KEY);
        break;
    case 'Guest':
    default:
        $accessible_modules = ['dashboard' => $all_modules['dashboard']];
        break;
}

// Determine current module to display
$current_module = $_GET['module'] ?? 'dashboard';

// Check if the requested module is accessible and exists
if (!array_key_exists($current_module, $accessible_modules)) {
    $current_module = 'dashboard'; // Fallback to dashboard if not accessible or invalid
}

$module_file = __DIR__ . '/modules/' . $current_module . '.php';
$dashboard_file = __DIR__ . '/modules/dashboard.php'; // Path to your actual dashboard file
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
    <style>
        /* Define CSS Variables for Light Mode (default) */
        :root {
            --bg-primary: #ecf0f3; /* Light gray background */
            --text-default: #333333; /* Dark text */
            --bg-glass: rgba(236, 240, 243, 0.8); /* Light glass background */
            --shadow-outer-dark: rgba(174,174,192,0.4); /* Dark shadow for light neumorphism */
            --shadow-outer-light: rgba(255,255,255,0.7); /* Light shadow for light neumorphism */
            --shadow-inset-dark: rgba(255,255,255,0.7); /* Inset light highlight */
            --shadow-inset-light: rgba(174,174,192,0.4); /* Inset dark shadow */

            --neon-cyan: #00A3A0; /* Slightly darker cyan for readability on light */
            --neon-magenta: #CC00CC; /* Slightly darker magenta */
            --neon-yellow: #CCCC00; /* Slightly darker yellow */

            --menu-item-bg-start: rgba(0,0,0,0.05); /* Very subtle dark overlay */
            --menu-item-bg-end: rgba(0,0,0,0.01);
            --menu-item-shadow-1: rgba(0,0,0,0.1);
            --menu-item-shadow-2: rgba(255,255,255,0.8);

            --menu-item-active-bg-start: rgba(255,255,0,0.1);
            --menu-item-active-bg-end: rgba(255,255,0,0.05);

            --panel-shadow-outer: 0 8px 25px rgba(0,0,0,0.1);
            --panel-shadow-inset: inset 0 0 15px rgba(0,0,0,0.05);
            --panel-border: none;
        }

        /* Dark Mode overrides */
        .dark {
            --bg-primary: #1C2526; /* Deep dark gray background */
            --text-default: #FFFFFF; /* White text */
            --bg-glass: rgba(28, 37, 38, 0.8); /* Dark glass background */
            --shadow-outer-dark: rgba(0,0,0,0.7); /* Dark shadow for dark neumorphism */
            --shadow-outer-light: rgba(255,255,255,0.1); /* Light shadow for dark neumorphism (subtle) */
            --shadow-inset-dark: rgba(0,255,255,0.4); /* Neon inner glow */
            --shadow-inset-light: rgba(0,0,0,0.3); /* Inset dark shadow */

            --neon-cyan: #00FFFF;
            --neon-magenta: #FF00FF;
            --neon-yellow: #FFFF00;

            --menu-item-bg-start: rgba(255,255,255,0.1);
            --menu-item-bg-end: rgba(255,255,255,0.03);
            --menu-item-shadow-1: rgba(0,0,0,0.3);
            --menu-item-shadow-2: rgba(255,255,255,0.1);

            --menu-item-active-bg-start: rgba(255,255,0,0.3);
            --menu-item-active-bg-end: rgba(255,255,0,0.15);

            --panel-shadow-outer: 0 8px 25px rgba(0,0,0,0.7);
            --panel-shadow-inset: inset 0 0 15px var(--neon-cyan); /* Use neon glow for dark glass */
            --panel-border: none;
        }

        /* ── Apply variables to main elements ── */
        body {
            background-color: var(--bg-primary);
            color: var(--text-default);
            transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transition for theme change */
        }

        .glass {
            background: var(--bg-glass);
            border: var(--panel-border);
            box-shadow: var(--panel-shadow-outer), var(--panel-shadow-inset);
            backdrop-filter: blur(10px); /* Glass effect */
            -webkit-backdrop-filter: blur(10px);
        }
        @supports not (backdrop-filter: blur(10px)) {
            .glass {
                /* Fallback for browsers not supporting backdrop-filter */
                background: var(--bg-primary); /* Just the transparent background */
            }
            .dark .glass {
                 /* Fallback for dark mode */
                 background: var(--bg-primary);
            }
        }

        .menu-item {
            background: linear-gradient(145deg, var(--menu-item-bg-start), var(--menu-item-bg-end));
            border-radius: 8px;
            /* Neumorphic shadows for a raised effect */
            box-shadow: 6px 6px 12px var(--menu-item-shadow-1), -6px -6px 12px var(--menu-item-shadow-2);
            transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
        }
        .menu-item.active {
            background: linear-gradient(145deg, var(--menu-item-active-bg-start), var(--menu-item-active-bg-end));
            /* Inset shadow for a "pressed" effect on active state */
            box-shadow: inset 3px 3px 6px var(--menu-item-shadow-1), inset -3px -3px 6px var(--menu-item-shadow-2);
        }
        /* Neumorphic press effect for menu items */
        .menu-item:not(.active):hover {
            transform: translateY(-2px); /* Slight lift on hover */
            box-shadow: 8px 8px 16px var(--menu-item-shadow-1), -8px -8px 16px var(--menu-item-shadow-2); /* More prominent hover shadow */
        }
        .menu-item:not(.active):active {
            transform: translateY(1px); /* Slight sink on click */
            box-shadow: inset 3px 3px 6px var(--menu-item-shadow-1), inset -3px -3px 6px var(--menu-item-shadow-2); /* Inset shadow for pressed state */
        }

        /* ── ADJUSTED: `.floating-module` for proper "floating" within its parent container ── */
        .floating-module {
            position: absolute; /* Keeps it positioned relative to its parent (module-area-80) */
            top: 1rem;    /* Aligns with padding of its parent */
            left: 1rem;   /* Aligns with padding of its parent */
            right: 1rem;  /* Aligns with padding of its parent */
            bottom: 1rem; /* Aligns with padding of its parent */
            z-index: 20; /* Ensures it's above other module content */
            background: var(--bg-glass); /* Use glass base color */
            border-radius: 8px;
            /* Neumorphic/Glassmorphic shadow for the float, using yellow neon for its glow */
            box-shadow: var(--panel-shadow-outer), inset 0 0 15px var(--neon-yellow);
            padding: 1.5rem; /* Internal padding for the module's content */
            overflow-y: auto;
            box-sizing: border-box; /* Crucial for including padding in total size */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        /* ── Styles for the overall main content area ── */
        main.glass {
            position: relative; /* Crucial for positioning absolute children (if any directly inside main) */
            overflow-x: hidden; /* Prevent horizontal scroll from padding etc. */
            box-sizing: border-box;
        }

        /* ── Styles for the 20% static dashboard section ── */
        .dashboard-static-20 {
            height: 20%; /* Takes 20% of its flex parent's height */
            flex-shrink: 0; /* Prevents it from shrinking */
            overflow-y: auto; /* Allows internal scrolling if content overflows */
        }

        /* ── Styles for the 80% dynamic module section ── */
        .module-area-80 {
            /* flex-1 from Tailwind will make it take remaining height */
            position: relative; /* Crucial for `.floating-module` to position itself correctly within this area */
            overflow-y: auto; /* Allows internal scrolling for module content */
        }

        /* Neon text colors using variables */
        .text-cyan-neon { color: var(--neon-cyan); }
        .text-magenta-neon { color: var(--neon-magenta); }
        .text-yellow-neon { color: var(--neon-yellow); }

        /* Additional neumorphic styling for form elements (e.g., select) */
        select {
            background-color: var(--bg-glass);
            border: none;
            box-shadow: inset 2px 2px 5px var(--shadow-inset-light), inset -3px -3px 5px var(--shadow-inset-dark);
            color: var(--text-default);
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
        }
        select:focus {
            outline: none;
            box-shadow: inset 2px 2px 5px var(--shadow-inset-light), inset -3px -3px 5px var(--shadow-inset-dark), 0 0 0 2px var(--neon-cyan);
        }

        /* Theme toggle button styling */
        #theme-toggle {
            background-color: var(--bg-glass); /* Glass background for toggle */
            border-radius: 9999px; /* Make it perfectly round */
            width: 2.5rem; /* Fixed width and height for a button */
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Neumorphic shadow for the button */
            box-shadow: 3px 3px 6px var(--menu-item-shadow-1), -3px -3px 6px var(--menu-item-shadow-2);
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
            color: var(--neon-yellow); /* Icon color */
        }
        #theme-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 4px 4px 8px var(--menu-item-shadow-1), -4px -4px 8px var(--menu-item-shadow-2);
        }
        #theme-toggle:active {
            transform: translateY(0);
            box-shadow: inset 2px 2px 5px var(--menu-item-shadow-1), inset -2px -2px 5px var(--menu-item-shadow-2);
        }
    </style>
</head>
<body class="min-h-screen font-sans flex flex-col">
    <header class="glass p-4 fixed w-full top-0 z-10 h-16 flex justify-between items-center">
        <h1 class="text-2xl text-cyan-neon">MPSM Control Panel</h1>
        <div class="flex items-center space-x-4">
            <form method="POST" action="" class="inline">
                <select name="role" onchange="this.form.submit()">
                    <?php foreach (['Developer', 'Admin', 'Service', 'Sales', 'Guest'] as $r): ?>
                        <option value="<?php echo $r; ?>" <?php echo $role === $r ? 'selected' : ''; ?>>
                            <?php echo $r; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="logout.php" class="text-magenta-neon hover:text-magenta-400 transition-colors">Logout</a>
            <a href="?reset"    class="text-yellow-neon hover:text-yellow-400 transition-colors">Reset Setup</a>
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
                    <?php foreach ($accessible_modules as $module => $key): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>"
                               class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === $module ? 'active' : ''; ?>">
                                <?php
                                    // Icons array - using heroicons path data for simplicity
                                    $icons = [
                                        'users'        => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h2v-2a6 6 0 00-6-6H9a6 6 0 00-6 6v2H5m11-9a4 4 0 10-8 0 4 4 0 008 0z"></path></svg>', // Adjusted users icon
                                        'device-mobile'=> '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                                        'lock-closed'  => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>', // Adjusted lock icon
                                        'wrench'       => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>', // Adjusted wrench icon
                                        'home'         => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', // Basic home icon
                                        'shield-check' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.001 12.001 0 002 12c0 2.298.508 4.513 1.417 6.425C4.857 20.358 8.09 22 12 22s7.143-1.642 8.583-3.575C21.492 16.513 22 14.298 22 12c0-3.379-1.282-6.529-3.382-8.616z"></path></svg>', // Shield Check icon for Permissions
                                        'code'         => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>'  // Code icon for DevTools
                                    ];
                                echo $icons[$key['icon']] ?? '';
                                ?>
                                <span><?php echo $key['label']; ?></span>
                            </a>
                        </li>
                    <?php foreach ($accessible_modules as $module_key => $module_data):
                        if ($module_key === 'permissions' && in_array($role, ['Developer', 'Admin'])): // Only show 'Permissions' for Developer/Admin
                    ?>
                        <li>
                            <a href="?module=permissions" class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === 'permissions' ? 'active' : ''; ?>">
                                <?php echo $icons['shield-check']; ?>
                                <span>Permissions</span>
                            </a>
                        </li>
                    <?php
                        endif;
                        if ($module_key === 'devtools' && in_array($role, ['Developer', 'Admin'])): // Only show 'DevTools' for Developer/Admin
                    ?>
                        <li>
                            <a href="?module=devtools" class="flex items-center p-2 rounded-lg menu-item <?php echo $current_module === 'devtools' ? 'active' : ''; ?>">
                                <?php echo $icons['code']; ?>
                                <span>DevTools</span>
                            </a>
                        </li>
                    <?php
                        endif;
                    endforeach; ?>
                </ul>
            </nav>
            <div class="mt-auto">
                <?php
                // Placeholder for status module content.
                // In a real application, you'd include the actual status.php file here.
                // For this example, we'll just show a placeholder.
                echo '<div class="glass p-4 rounded-lg mt-4 text-sm text-center">';
                echo '<h3 class="text-md text-yellow-neon mb-2">System Status</h3>';
                echo '<p>All systems <span class="text-cyan-neon font-bold">Online</span></p>';
                echo '<p>Role: <span class="font-semibold text-yellow-neon">' . htmlspecialchars($role) . '</span></p>';
                echo '</div>';
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
                // Only load other modules in this area if the current module is NOT dashboard.
                // If dashboard is selected, this area remains empty or shows a specific message.
                if ($current_module !== 'dashboard' && file_exists($module_file)) {
                    include $module_file; // Loads specific module content (e.g., customers, devices)
                } else if ($current_module === 'dashboard') {
                    // Display a message when only the dashboard (top 20%) is active
                    echo '<p class="text-default">Select a module from the sidebar to view its content in this area.</p>';
                } else {
                    // This else block handles cases where a non-dashboard module was requested but not found or accessible.
                    echo '<p class="text-yellow-neon">Module not found or not accessible. Please select another module from the sidebar.</p>';
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement; // Target the <html> element for theme class

        // Function to set the theme
        function setTheme(theme) {
            if (theme === 'dark') {
                htmlElement.classList.add('dark');
                // Ensure correct icon is shown based on current theme state
                document.getElementById('moon-icon').style.display = 'inline-block';
                document.getElementById('sun-icon').style.display = 'none';
            } else {
                htmlElement.classList.remove('dark');
                // Ensure correct icon is shown based on current theme state
                document.getElementById('moon-icon').style.display = 'none';
                document.getElementById('sun-icon').style.display = 'inline-block';
            }
            localStorage.setItem('theme', theme);
        }

        // Get stored theme or default to system preference
        const storedTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const initialTheme = storedTheme || systemTheme; // Use stored, else system

        // Apply initial theme
        setTheme(initialTheme);

        // Add event listener to toggle button
        themeToggle.addEventListener('click', () => {
            // Check the current theme based on the class on the html element
            const currentTheme = htmlElement.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });

        // Listen for system theme changes (if no stored preference)
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) { // Only update if no explicit user preference
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    </script>
</body>
</html>