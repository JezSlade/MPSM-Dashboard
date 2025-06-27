<?php
// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define root path
define('ROOT_DIR', __DIR__);

// Load core files
require_once ROOT_DIR . '/../config.php';
require_once ROOT_DIR . '/../lib/ErrorHandler.php';
require_once ROOT_DIR . '/../lib/Database.php';

// Initialize error handling
ErrorHandler::initialize();

try {
    // Create database instance
    $db = new Database();
    
    // Fetch active widgets
    $stmt = $db->query("SELECT * FROM widgets WHERE is_active = 1 ORDER BY created_at");
    $widgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSRF token if needed
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    // Render header
    include ROOT_DIR . '/../templates/header.php';
    
    echo '<div class="widget-grid">';
    
    // Process each widget
    foreach ($widgets as $widget) {
        $safeType = preg_replace('/[^a-zA-Z0-9_-]/', '', $widget['type']);
        $widgetFile = ROOT_DIR . "/../widgets/core/{$safeType}.php";
        
        if (file_exists($widgetFile)) {
            echo '<div class="widget" data-id="' . htmlspecialchars($widget['id'], ENT_QUOTES) . '">';
            
            // Extract settings
            $settings = json_decode($widget['settings'] ?? '{}', true) ?: [];
            
            // Create config array
            $config = [
                'id' => $widget['id'],
                'title' => $widget['title'] ?? 'Untitled Widget',
                'type' => $widget['type'],
                'settings' => $settings,
                'code' => $widget['code'] ?? ''
            ];
            
            // Include widget with config
            include $widgetFile;
            
            echo '</div>';
        } else {
            error_log("Missing widget file: {$widget['type']}");
            echo '<div class="widget-error">Widget file not found</div>';
        }
    }
    
    echo '</div>';
    
    // Render footer
    include ROOT_DIR . '/../templates/footer.php';

} catch (Throwable $e) {
    // Handle all errors and exceptions
    ErrorHandler::handleException($e);
}