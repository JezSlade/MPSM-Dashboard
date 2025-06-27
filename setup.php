<?php
// setup.php - Self-contained initialization wizard

// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/ErrorHandler.php';

session_start();
ErrorHandler::initialize();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if setup is already complete
if (file_exists(DB_FILE) && filesize(DB_FILE) > 0) {
    header('Location: /dashboard/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
    
    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $db->exec("PRAGMA journal_mode = WAL");
        $db->exec("CREATE TABLE widgets (
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
        
        $db->exec("CREATE TABLE settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL,
            is_public BOOLEAN DEFAULT 0,
            description TEXT
        )");
        
        $db->exec("INSERT INTO settings (key, value, is_public, description) VALUES
            ('schema_version', '" . DB_SCHEMA_VERSION . "', 0, 'Database schema version'),
            ('APP_NAME', '" . APP_NAME . "', 1, 'Application name')");
            
        header('Location: /dashboard/');
        exit;
    } catch (PDOException $e) {
        $error = "Database setup failed: " . $e->getMessage();
    }
}

// Render setup form
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; }
        .card { background: rgba(30, 30, 45, 0.8); padding: 2rem; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        button { background: #3a86ff; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1>System Setup</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert" style="color: #ff3860; margin-bottom: 1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <p>This will initialize the database and create the required tables.</p>
            </div>
            
            <button type="submit">Initialize Database</button>
        </form>
    </div>
</body>
</html>