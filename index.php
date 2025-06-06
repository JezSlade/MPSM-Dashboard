<?php
// index.php
// ────────────────────────────────────────────────────────────────────────────────
// Main entry for MPSM Dashboard
// Assumes session/auth logic sets these variables before including anything:
//   - $accessible_modules    (array of ['module_key' => ['label' => 'Name', 'icon' => '…']])
//   - $current_module        (string, the module key currently selected)
//   - $dashboard_file        (full filesystem path to whichever dashboard view to include)
//   - $module_file           (full filesystem path to whichever module to include, if any)
//
// Make sure you set those up in your bootstrap or routing logic above this line.
// Example (not shown here):
//   session_start();
//   $accessible_modules = [ 'status'=> ['label'=>'Status','icon'=>'status'], 'devtools'=>['label'=>'DevTools','icon'=>'devtools'] ];
//   $current_module = $_GET['module'] ?? 'status';
//   $dashboard_file = __DIR__ . '/dashboards/main.php';
//   $module_file = isset($_GET['module']) ? __DIR__ . '/modules/' . $_GET['module'] . '.php' : null;
//   if ($module_file && !file_exists($module_file)) { $module_file = null; }
//   // …authentication, permission checks, etc.

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPS Monitor Dashboard</title>

    <!-- 1) Load custom styles first -->
    <link rel="stylesheet" href="styles.css">
    <!-- 2) Fallback styles (if Tailwind fails) -->
    <link rel="stylesheet" href="styles-fallback.css">
    <!-- 3) Tailwind can augment, but our overrides take precedence -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <h1>Resolutions by Design • MPSM Dashboard</h1>
        <nav>
            <a href="?module=devtools">DevTools</a>
            <a href="?module=status">Status</a>
            <a href="?logout">Logout</a>
        </nav>
    </header>

    <div class="flex mt-16">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <nav>
                <ul class="space-y-2">
                    <?php foreach ($accessible_modules as $module => $key): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>"
                               class="menu-item <?php echo ($current_module === $module) ? 'active' : ''; ?>">
                                <?php
                                  // Example icon logic. Replace these SVGs with your own if desired.
                                  $icons = [
                                    'status'   => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
                                    'devtools' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                                    // …add other icons for other modules here…
                                  ];
                                  echo $icons[$module] ?? '';
                                ?>
                                <span><?php echo $key['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Status Module at bottom -->
            <div class="mt-auto">
                <?php
                  // Adjust path if your modules folder is elsewhere
                  include_once __DIR__ . '/modules/status.php';
                ?>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <?php
              // This is your “dashboard” view (e.g. overview charts, stats, etc.)
              include $dashboard_file;
            ?>

            <?php if (!empty($module_file)): ?>
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
