<?php
<<<<<<< HEAD
if (file_exists(__DIR__ . '/install.php')) {
    header('Location: install.php');
    exit;
}

require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/widgets.php';
require_login();
=======
require_once 'core/config.php';
require_once 'core/auth.php';
require_once 'core/widget_registry.php';
>>>>>>> 4b9007029866c446bde310faaf45fc114177158a

// Require login
Auth::requireLogin();

$user_id = Auth::getCurrentUserId();
$widget_registry = WidgetRegistry::getInstance();
$user_widgets = $widget_registry->get_user_widgets($user_id);

// Get user's widget layout preferences
$stmt = $db->prepare("SELECT layout FROM user_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$layout = $stmt->fetchColumn();

if (!$layout) {
    // Default layout
    $layout = json_encode([
        'dashboard' => array_keys($user_widgets)
    ]);
}

$layout = json_decode($layout, true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>MPSM Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <h1>MPSM Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="user-menu">
                    <span class="user-greeting">Welcome, <?php echo htmlspecialchars(Auth::getCurrentUser()); ?></span>
                    <div class="user-actions">
                        <?php if (Auth::isDeveloper()): ?>
                            <a href="developer_console.php" class="btn btn-sm">Developer Console</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="app-main">
            <div class="dashboard-actions">
                <a href="dashboard_customize.php" class="btn btn-primary">Customize Dashboard</a>
            </div>
            
            <div class="dashboard" id="dashboard">
                <?php 
                // Render widgets based on layout
                if (isset($layout['dashboard'])) {
                    foreach ($layout['dashboard'] as $widget_id) {
                        if (isset($user_widgets[$widget_id])) {
                            $widget = $widget_registry->get_widget($widget_id);
                            if ($widget) {
                                echo '<div class="widget-container" data-widget-id="' . $widget_id . '">';
                                echo $widget->render();
                                echo '<div class="widget-actions">';
                                echo '<button class="refresh-widget" title="Refresh"><i class="refresh-icon">↻</i></button>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    }
                }
                ?>
            </div>
        </main>
        
        <footer class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> MPSM Dashboard. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
