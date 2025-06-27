<?php
// Disable default error display
ini_set('display_errors', '0');

// Simple error handler for setup
function handleSetupError($errno, $errstr, $errfile, $errline) {
    echo "<script>document.getElementById('progress').innerHTML += '<li style=\"color:red\">ERROR: " . 
         htmlspecialchars($errstr) . " in " . htmlspecialchars($errfile) . " on line " . 
         htmlspecialchars($errline) . "</li>';</script>";
    ob_flush();
    flush();
    return true;
}
set_error_handler('handleSetupError');

// Start output buffering
ob_start();
@ini_set('zlib.output_compression', 0);
ob_implicit_flush(true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a2e; color: #e0f7fa; padding: 20px; }
        .card { background: rgba(30,30,45,0.8); padding: 20px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
        #progress { background: rgba(0,0,0,0.2); border-radius: 5px; padding: 15px; margin: 15px 0; max-height: 300px; overflow-y: auto; }
        #progress li { margin: 5px 0; padding: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); list-style-type: none; }
        .success { color: #0af; }
        .error { color: #f55; }
    </style>
</head>
<body>
    <div class="card">
        <h1>System Setup</h1>
        <div id="progress"></div>
    </div>
    <script>
    function addProgress(message, isError = false) {
        const li = document.createElement('li');
        li.className = isError ? 'error' : '';
        li.textContent = message;
        document.getElementById('progress').appendChild(li);
        document.getElementById('progress').scrollTop = document.getElementById('progress').scrollHeight;
    }
    </script>
    <?php
    function progress($message) {
        echo "<script>addProgress(" . json_encode($message) . ");</script>";
        ob_flush();
        flush();
        usleep(100000); // Small delay for visibility
    }

    try {
        progress("Starting setup process...");

        // Load config manually with fallbacks
        if (!file_exists(__DIR__ . '/config.php')) {
            throw new Exception("config.php file not found");
        }
        
        // Define minimal constants if not defined
        if (!defined('DB_DIR')) define('DB_DIR', __DIR__ . '/db');
        if (!defined('DB_FILE')) define('DB_FILE', DB_DIR . '/cms.db');
        
        progress("Creating database directory...");
        if (!file_exists(DB_DIR)) {
            if (!mkdir(DB_DIR, 0755, true)) {
                throw new Exception("Could not create database directory");
            }
            file_put_contents(DB_DIR . '/.htaccess', "Deny from all");
            progress("✓ Database directory created");
        }

        progress("Initializing database connection...");
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        progress("Creating database tables...");
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
        
        progress("Creating settings table...");
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL,
            is_public BOOLEAN DEFAULT 0,
            description TEXT
        )");
        
        progress("Loading default configuration...");
        require __DIR__ . '/config.php';
        
        progress("Inserting initial settings...");
        $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value, is_public, description) VALUES (?, ?, ?, ?)");
        $stmt->execute(['schema_version', '1.0', 0, 'Database schema version']);
        $stmt->execute(['APP_NAME', APP_NAME, 1, 'Application name']);
        $stmt->execute(['DEBUG_MODE', DEBUG_MODE ? '1' : '0', 0, 'Debug mode']);
        
        progress("Finalizing setup...");
        file_put_contents(DB_DIR . '/.setup_complete', date('Y-m-d H:i:s'));
        
        progress("✓ Setup completed successfully!", true);
        echo '<script>setTimeout(() => window.location.href = "/dashboard/", 2000);</script>';
        
    } catch (Exception $e) {
        progress("✗ ERROR: " . $e->getMessage(), true);
        progress("Setup failed. Please check permissions and try again.", true);
    }
    ?>
</body>
</html>