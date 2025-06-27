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

// Define root path and setup constants
define('ROOT_DIR', __DIR__);
define('SETUP_COMPLETE_FILE', ROOT_DIR . '/db/.setup_complete');
define('SETUP_SCRIPT', ROOT_DIR . '/setup.php');

// Check if setup needs to run
if (!file_exists(SETUP_COMPLETE_FILE)) {
    define('IN_SETUP_MODE', true);
    // Redirect to setup if not completed
    if (!defined('IN_SETUP_MODE')) {
        header('Location: setup.php');
        exit;
    }
    // If we're already in setup mode but setup isn't complete, show error
    die("System setup is required. Please complete the setup process first.");
}

// Load core files
require_once __DIR__ . '/config.php';
require_once ROOT_DIR . '/../lib/ErrorHandler.php';
require_once ROOT_DIR . '/../lib/Database.php';

// Initialize error handling
ErrorHandler::initialize();

try {
    // Create database instance
    $db = new Database();
    
    // Verify database schema version
    $requiredVersion = '1.0'; // Your required schema version
    $currentVersion = $db->query("SELECT value FROM settings WHERE key = 'schema_version'")
                         ->fetchColumn();
    
    if ($currentVersion !== $requiredVersion) {
        die("Database schema mismatch. Please run setup again or contact support.");
    }
    
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