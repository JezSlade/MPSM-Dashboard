<?php
// index.php
// ────────────────────────────────────────────────────────────────────────────────
// Main entry for MPSM Dashboard
//
// Before including this file, your bootstrap/routing logic must define:
//   • define('BASE_PATH', '/your/project/root/');   // e.g. '/mpsm/'
//   • session_start(), authentication, permission checks, etc.
//   • $accessible_modules: array like
//         [ 'status'=>['label'=>'Status','icon'=>'status'], 'devtools'=>['label'=>'DevTools','icon'=>'devtools'], … ]
//   • $current_module:   string, e.g. 'status' or 'devtools'
//   • $dashboard_file:   full filesystem path to the dashboard view (e.g. __DIR__ . '/dashboards/main.php')
//   • $module_file:      full filesystem path to a module if one is selected
//                          (e.g. __DIR__ . '/modules/devtools.php'), or null if none
//
// Example (not included here):
//   define('BASE_PATH', '/mpsm/');
//   session_start();
//   $accessible_modules = [
//     'status'   => ['label'=>'Status','icon'=>'status'],
//     'devtools' => ['label'=>'DevTools','icon'=>'devtools'],
//     // … other modules …
//   ];
//   $current_module = $_GET['module'] ?? 'status';
//   $dashboard_file = __DIR__ . '/dashboards/main.php';
//   $module_file    = isset($_GET['module'])
//                        ? __DIR__ . '/modules/' . $_GET['module'] . '.php'
//                        : null;
//   if ($module_file && ! file_exists($module_file)) {
//     $module_file = null;
//   }
//   // … permission checks, etc.

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPS Monitor Dashboard</title>

    <!-- 1) Load our custom Neumorphic/CMYK theme first -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles.css">
    <!-- 2) Tailwind (our CSS overrides take precedence) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- ── HEADER ─────────────────────────────────────────────────────────────────── -->
    <header class="header">
        <h1>Resolutions by Design • MPSM Dashboard</h1>
        <nav>
            <a href="?module=devtools">DevTools</a>
            <a href="?module=status">Status</a>
            <a href="?logout">Logout</a>
        </nav>
    </header>

    <!-- ── LAYOUT: Sidebar + Main Content ──────────────────────────────────────────── -->
    <div class="flex mt-16">
        <!-- ─── SIDEBAR (glass, fixed) ──────────────────────────────────────────────── -->
        <aside class="glass w-64 p-4 fixed h-[calc(100vh-80px)] top-16 overflow-y-auto flex flex-col">
            <nav>
                <ul class="space-y-2">
                    <?php foreach ($accessible_modules as $module => $meta): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>"
                               class="menu-item <?php echo ($current_module === $module) ? 'active' : ''; ?>">
                                <?php
                                  // SVG icons by module key (replace or expand as needed)
                                  $icons = [
                                    'status'   => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>',
                                    'devtools' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0
                                                               11-18 0 9 9 0 0118 0z" />
                                                    </svg>',
                                    // …add other module icons here…
                                  ];
                                  echo $icons[$module] ?? '';
                                ?>
                                <span><?php echo $meta['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Status Module (always visible at the bottom of the sidebar) -->
            <div class="mt-auto">
                <?php include_once __DIR__ . '/modules/status.php'; ?>
            </div>
        </aside>

        <!-- ─── MAIN CONTENT (glass) ─────────────────────────────────────────────────── -->
        <main class="glass flex-1 p-6 ml-64 mt-16 relative">
            <!-- Dashboard view (charts, stats, etc.) -->
            <?php include $dashboard_file; ?>

            <!-- If a module is selected, load it in a floating modal above the dashboard -->
            <?php if (! empty($module_file)): ?>
                <div class="floating-module">
                    <?php include $module_file; ?>
                    <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>"
                       class="text-yellow-neon mt-4 inline-block">Close</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
