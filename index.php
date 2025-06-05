<?php
// index.php
// ────────────────────────────────────────────────────────────────────────────────
// Main entry for MPSM Dashboard
// Assumes BASE_PATH is defined, and $accessible_modules / $current_module / $dashboard_file / $module_file are set

// Example at top of file (not shown here):
// define('BASE_PATH', '/path/to/your/project/');
// session_start();
// … authentication, permission checks, etc.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPS Monitor Dashboard</title>

    <!-- 1a. Load our custom styles first -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles.css">
    <!-- 1b. Keep the fallback in case Tailwind isn’t available -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles-fallback.css">
    <!-- 1c. Tailwind can still augment things, but our overrides come first -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h1>Resolutions by Design • MPSM Dashboard</h1>
        <nav>
            <a href="?module=devtools">DevTools</a>
            <a href="?module=status">Status</a>
            <a href="?logout">Logout</a>
        </nav>
    </header>

    <div class="flex mt-16">
        <!-- Sidebar -->
        <aside class="sidebar">
            <nav>
                <ul class="space-y-2">
                    <?php foreach ($accessible_modules as $module => $key): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>"
                               class="menu-item <?php echo $current_module === $module ? 'active' : ''; ?>">
                                <?php
                                  // Example icon logic; replace with your SVGs as needed
                                  $icons = [
                                    'status'   => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
                                    'devtools' => '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                                    // …add other icons here…
                                  ];
                                  echo $icons[$module] ?? '';
                                ?>
                                <span><?php echo $key['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Status Module (always visible at bottom of sidebar) -->
            <div class="mt-auto">
                <?php include_once BASE_PATH . 'modules/status.php'; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php include $dashboard_file; ?>

            <?php if ($module_file): ?>
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
