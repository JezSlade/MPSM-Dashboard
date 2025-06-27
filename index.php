<?php
// index.php - Self-initializing entry point
// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

// Define absolute paths
define('ROOT_DIR', __DIR__);
define('DB_FILE', ROOT_DIR . '/db/cms.db');
define('DB_DIR', ROOT_DIR . '/db');

// Check and create directory structure
if (!file_exists(DB_DIR)) {
    @mkdir(DB_DIR, 0755, true);
    @file_put_contents(DB_DIR . '/.htaccess', "Deny from all"); // Security
}

// Initialize environment
require_once 'lib/ErrorHandler.php';
require_once 'lib/Database.php';

// Automatic database setup
try {
    $db = new Database();
    
    if ($db->needsSetup()) {
        if ($this->isSetupAllowed()) {
            $db->initializeSchema();
            $db->seedDefaultData();
            header('Location: /dashboard/');
            exit;
        } else {
            die($this->showSetupWarning());
        }
    }
    
    // Normal application flow
    if (str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
        require 'api/index.php';
    } else {
        require 'dashboard/index.php';
    }

} catch (Exception $e) {
    ErrorHandler::fatal($e);
}

// Helper methods
function isSetupAllowed(): bool {
    return !file_exists(DB_FILE) && 
           is_writable(DB_DIR) && 
           !isset($_GET['preventSetup']);
}

function showSetupWarning(): string {
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; }
        .warning { 
            background: #fff3cd; 
            border-left: 5px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="warning">
        <h2>Database Setup Required</h2>
        <p>The system needs to initialize its database.</p>
        
        <form method="post" action="/setup.php">
            <button type="submit">Initialize Database Now</button>
            <input type="hidden" name="confirm_setup" value="1">
        </form>
        
        <p><small>If you see this message unexpectedly, check directory permissions for the <code>/db</code> folder.</small></p>
    </div>
</body>
</html>
HTML;
}