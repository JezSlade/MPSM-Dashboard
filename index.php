<?php
require_once __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_SESSION['dashboard']['settings']['title']) ?></title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="dashboard">
        <!-- Dashboard Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="logo-text"><?= htmlspecialchars($_SESSION['dashboard']['settings']['title']) ?></div>
            </div>
            <div class="header-actions">
                <button class="btn" id="settings-toggle">
                    <i class="fas fa-cog"></i> Settings
                </button>
                <button class="btn" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-primary" id="new-widget-btn">
                    <i class="fas fa-plus"></i> New Widget
                </button>
            </div>
        </header>

        <!-- Dashboard Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="section-title">Navigation</div>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Analytics</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
            </div>
            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php
                    $available_widgets = [
                        'stats' => ['name' => 'Statistics', 'icon' => 'chart-bar'],
                        'tasks' => ['name' => 'Task Manager', 'icon' => 'tasks'],
                        'calendar' => ['name' => 'Calendar', 'icon' => 'calendar'],
                        'notes' => ['name' => 'Quick Notes', 'icon' => 'sticky-note'],
                        'activity' => ['name' => 'Recent Activity', 'icon' => 'history'],
                    ];
                    foreach ($available_widgets as $id => $widget): ?>
                        <div class="widget-item" draggable="true" data-widget-id="<?= $id ?>">
                            <i class="fas fa-<?= $widget['icon'] ?>"></i>
                            <div class="widget-name"><?= $widget['name'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="theme-settings-btn">
                    <i class="fas fa-palette"></i>
                    <span>Theme Settings</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-columns"></i>
                    <span>Layout Options</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content" id="widget-container">
            <?php
            $widgets = $_SESSION['dashboard']['widgets'];
            foreach ($widgets as $index => $widget):
                $widget_def = $available_widgets[$widget['id']] ?? ['icon' => 'cube'];
                $expanded_class = $widget['expanded'] ? 'expanded' : '';
            ?>
                <div class="widget <?= $expanded_class ?>" style="--width:1; --height:1">
                    <div class="widget-header">
                        <div class="widget-title" data-index="<?= $index ?>">
                            <i class="fas fa-<?= $widget_def['icon'] ?>"></i>
                            <span><?= htmlspecialchars($widget['title']) ?></span>
                        </div>
                        <div class="widget-actions">
                            <div class="widget-action settings-btn" data-index="<?= $index ?>">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="widget-action expand-btn" data-index="<?= $index ?>">
                                <i class="fas <?= $widget['expanded'] ? 'fa-compress' : 'fa-expand' ?>"></i>
                            </div>
                            <div class="widget-action remove-btn" data-index="<?= $index ?>">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content">
                        <?php
                        if (file_exists(__DIR__ . "/widgets/{$widget['id']}_widget.php")) {
                            include __DIR__ . "/widgets/{$widget['id']}_widget.php";
                        } else {
                            echo "<p>Missing widget: {$widget['id']}</p>";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Overlay and Panels -->
    <div class="overlay" id="settings-overlay"></div>
    <div class="settings-panel" id="settings-panel"></div>
    <div class="widget-settings-modal" id="widget-settings-modal"></div>

    <script src="public/js/scripts.js"></script>
</body>
</html>
