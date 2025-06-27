<?php
// =============================================
// Setup Configuration
// =============================================
define('ROOT_DIR', __DIR__);
define('SETUP_COMPLETE_FILE', ROOT_DIR . '/db/.setup_complete');
define('CONFIG_FILE', ROOT_DIR . '/config.php');
define('DB_FILE', ROOT_DIR . '/db/cms.db');
define('REQUIRED_SCHEMA_VERSION', '1.0');

// =============================================
// Initialization
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if setup is already complete
if (file_exists(SETUP_COMPLETE_FILE)) {
    header('Location: index.php');
    exit;
}

// =============================================
// Setup Functions
// =============================================
function log_progress($message, $is_error = false) {
    $_SESSION['setup_log'][] = [
        'message' => $message,
        'is_error' => $is_error,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    error_log('SETUP: ' . $message);
}

function check_requirements() {
    $errors = [];
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP 7.4 or higher is required';
    }
    
    // Check extensions
    $required_extensions = ['pdo', 'pdo_sqlite', 'json', 'session'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "PHP extension '{$ext}' is required";
        }
    }
    
    // Check directory permissions
    $writable_dirs = [ROOT_DIR . '/db', ROOT_DIR . '/widgets', ROOT_DIR . '/templates'];
    foreach ($writable_dirs as $dir) {
        if (!is_writable($dir) && !@mkdir($dir, 0755, true)) {
            $errors[] = "Directory '{$dir}' must be writable";
        }
    }
    
    return $errors;
}

function initialize_database() {
    try {
        // Create database directory if needed
        if (!file_exists(dirname(DB_FILE))) {
            if (!mkdir(dirname(DB_FILE), 0755, true)) {
                throw new Exception('Failed to create database directory');
            }
            file_put_contents(dirname(DB_FILE) . '/.htaccess', "Deny from all");
            log_progress('Created database directory');
        }
        
        // Connect to database
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
        $db->exec("PRAGMA journal_mode = WAL");
        $db->exec("CREATE TABLE IF NOT EXISTS widgets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL DEFAULT 'New Widget',
            type TEXT NOT NULL,
            x INTEGER NOT NULL DEFAULT 0,
            y INTEGER NOT NULL DEFAULT 0,
            width INTEGER NOT NULL DEFAULT 4,
            height INTEGER NOT NULL DEFAULT 4,
            settings TEXT DEFAULT '{}',
            code TEXT,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL,
            is_public BOOLEAN DEFAULT 0,
            description TEXT
        )");
        
        // Insert initial settings
        $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value, is_public, description) VALUES (?, ?, ?, ?)");
        $stmt->execute(['schema_version', REQUIRED_SCHEMA_VERSION, 0, 'Database schema version']);
        $stmt->execute(['APP_NAME', 'MPS Widget CMS', 1, 'Application name']);
        $stmt->execute(['DEBUG_MODE', '0', 0, 'Debug mode']);
        
        log_progress('Database initialized successfully');
        return $db;
    } catch (PDOException $e) {
        log_progress('Database error: ' . $e->getMessage(), true);
        throw $e;
    }
}

// =============================================
// Setup Process
// =============================================
try {
    // Initialize setup log
    if (empty($_SESSION['setup_log'])) {
        $_SESSION['setup_log'] = [];
    }
    
    // Process setup steps
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $step = $_POST['step'] ?? 1;
        
        switch ($step) {
            case 1: // Requirements check
                $errors = check_requirements();
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        log_progress($error, true);
                    }
                    throw new Exception('System requirements not met');
                }
                log_progress('System requirements verified');
                break;
                
            case 2: // Database initialization
                $db = initialize_database();
                break;
                
            case 3: // Finalize setup
                file_put_contents(SETUP_COMPLETE_FILE, date('Y-m-d H:i:s'));
                log_progress('Setup completed successfully');
                header('Location: index.php');
                exit;
        }
        
        // Move to next step
        $_SESSION['setup_step'] = $step + 1;
        header('Location: setup.php');
        exit;
    }
    
    // Get current step
    $current_step = $_SESSION['setup_step'] ?? 1;
    
} catch (Exception $e) {
    log_progress('Setup failed: ' . $e->getMessage(), true);
    $_SESSION['setup_error'] = true;
}

// =============================================
// HTML Output
// =============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .progress-bar { height: 20px; background: #eee; border-radius: 10px; margin: 20px 0; overflow: hidden; }
        .progress { height: 100%; background: #4CAF50; width: <?= ($current_step-1)*33 ?>%; transition: width 0.3s; }
        .log { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 20px 0; background: #fafafa; }
        .log-entry { margin-bottom: 5px; padding: 5px; border-bottom: 1px solid #eee; }
        .error { color: #d9534f; }
        .success { color: #5cb85c; }
        button { background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        button:disabled { background: #cccccc; }
    </style>
</head>
<body>
    <div class="container">
        <h1>System Setup</h1>
        
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
        
        <?php if (!empty($_SESSION['setup_error'])): ?>
            <div class="alert alert-danger">
                Setup encountered errors. Please check the log below and try again.
            </div>
        <?php endif; ?>
        
        <div class="log">
            <?php foreach ($_SESSION['setup_log'] ?? [] as $entry): ?>
                <div class="log-entry <?= $entry['is_error'] ? 'error' : 'success' ?>">
                    [<?= $entry['timestamp'] ?>] <?= htmlspecialchars($entry['message']) ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form method="post">
            <input type="hidden" name="step" value="<?= $current_step ?>">
            
            <?php if ($current_step == 1): ?>
                <h2>System Requirements Check</h2>
                <p>The setup will now verify your server meets all requirements.</p>
                
            <?php elseif ($current_step == 2): ?>
                <h2>Database Initialization</h2>
                <p>The setup will now create and configure the database.</p>
                
            <?php elseif ($current_step == 3): ?>
                <h2>Finalize Setup</h2>
                <p>Complete the setup process and launch your application.</p>
            <?php endif; ?>
            
            <button type="submit" <?= !empty($_SESSION['setup_error']) ? 'disabled' : '' ?>>
                <?= $current_step < 3 ? 'Continue' : 'Finish Setup' ?>
            </button>
            
            <?php if (!empty($_SESSION['setup_error'])): ?>
                <a href="setup.php?reset=1" class="button">Restart Setup</a>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>