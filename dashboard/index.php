<?php
// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ErrorHandler.php';
require_once __DIR__ . '/../lib/Database.php';

ErrorHandler::initialize();

try {
    $db = new Database();
    $widgets = $db->query("SELECT * FROM widgets WHERE is_active = 1 ORDER BY created_at")->fetchAll();
    
    // Generate CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    // Render header
    include __DIR__ . '/../templates/header.php';
    
    echo '<div class="widget-grid">';
    foreach ($widgets as $widget) {
        $safeType = preg_replace('/[^a-zA-Z0-9_-]/', '', $widget['type']);
        $widgetFile = __DIR__ . "/../widgets/core/{$safeType}.php";
        
        if (file_exists($widgetFile)) {
            echo '<div class="widget" data-id="' . htmlspecialchars($widget['id'], ENT_QUOTES) . '">';
            include $widgetFile;
            echo '</div>';
        } else {
            ErrorHandler::log("Missing widget file: {$widget['type']}");
            echo '<div class="widget-error">Widget not found</div>';
        }
    }
    echo '</div>';
    
    // Render footer
    include __DIR__ . '/../templates/footer.php';

} catch (Exception $e) {
    ErrorHandler::handleException($e);
}