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

// Check if setup is already complete
if (file_exists(DB_FILE) && filesize(DB_FILE) > 0) {
    header('Location: /dashboard/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    
    try {
        // Create database directory if needed
        if (!file_exists(DB_DIR)) {
            mkdir(DB_DIR, 0755, true);
            file_put_contents(DB_DIR . '/.htaccess', "Deny from all");
        }
        
        // Initialize database
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
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
        
        // Insert initial settings
        $defaultSettings = [
            ['schema_version', DB_SCHEMA_VERSION, 0, 'Database schema version'],
            ['APP_NAME', APP_NAME, 1, 'Application name'],
            ['DEBUG_MODE', DEBUG_MODE ? '1' : '0', 0, 'Debug mode'],
            ['TIMEZONE', TIMEZONE, 1, 'System timezone']
        ];
        
        $stmt = $db->prepare("INSERT INTO settings (key, value, is_public, description) VALUES (?, ?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        // Create setup complete flag
        file_put_contents(DB_DIR . '/.setup_complete', '1');
        
        header('Location: /dashboard/');
        exit;
    } catch (PDOException $e) {
        $error = "Database setup failed: " . $e->getMessage();
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Setup</title>
    <style>
        :root {
            --glass-bg: rgba(30, 30, 45, 0.7);
            --neon-accent: #0af;
            --text-primary: #e0f7fa;
        }
        body {
            background: radial-gradient(circle at center, #1a1a2e, #16213e);
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text-primary);
        }
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                inset 2px 2px 5px rgba(255, 255, 255, 0.1),
                inset -2px -2px 5px rgba(0, 0, 0, 0.5),
                5px 5px 15px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--neon-accent);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0088cc;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            background: rgba(255, 50, 50, 0.2);
            border-left: 3px solid #ff3860;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>System Setup</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <p>This will initialize the SQLite database and create required tables.</p>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn">Initialize Database</button>
        </form>
    </div>
</body>
</html>