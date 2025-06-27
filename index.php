<?php
session_start();
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/WidgetManager.php';

// Initialize dashboard
$widgetManager = new WidgetManager();
$settings = getDashboardSettings();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__.'/includes/handle_form.php';
}

// Render the dashboard
require_once __DIR__.'/includes/header.php';
require_once __DIR__.'/includes/sidebar.php';
?>

<main class="main-content" id="widget-container">
    <?php foreach ($widgetManager->getActiveWidgets() as $index => $widget): ?>
        <?= $widgetManager->renderWidget($widget, $index) ?>
    <?php endforeach; ?>
</main>

<?php 
require_once __DIR__.'/includes/settings_panel.php';
require_once __DIR__.'/includes/footer.php';
?>
