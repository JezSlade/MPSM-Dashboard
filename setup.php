<?php
// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

// Start output buffering for progress streaming
ob_start();
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
ob_implicit_flush(1);

// Force immediate output
function progress($message) {
    echo "<script>document.getElementById('progress').innerHTML += '<li>$message</li>';</script>";
    ob_flush();
    flush();
    usleep(50000); // Small delay for better visibility
}

?><!DOCTYPE html>
<html>
<head>
    <title>System Setup</title>
    <style>
        /* ... (keep your existing styles) ... */
        #progress {
            background: rgba(0,0,0,0.2);
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        #progress li {
            margin: 5px 0;
            list-style-type: none;
            padding: 3px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>System Setup</h1>
        <div id="progress"></div>
        <?php
        try {
            progress("Starting setup process...");
            
            // Load config manually since ErrorHandler isn't available yet
            if (!file_exists(__DIR__ . '/config.php')) {
                throw new Exception("Missing config.php");
            }
            require __DIR__ . '/config.php';
            
            progress("Verifying database directory...");
            if (!file_exists(DB_DIR)) {
                if (!mkdir(DB_DIR, 0755, true)) {
                    throw new Exception("Failed to create database directory");
                }
                file_put_contents(DB_DIR . '/.htaccess', "Deny from all");
                progress("Created database directory");
            }

            progress("Initializing database...");
            $db = new PDO('sqlite:' . DB_FILE);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            progress("Creating tables...");
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
            
            progress("Inserting default settings...");
            $defaultSettings = [
                ['schema_version', DB_SCHEMA_VERSION, 0, 'Database schema version'],
                ['APP_NAME', APP_NAME, 1, 'Application name'],
                ['DEBUG_MODE', DEBUG_MODE ? '1' : '0', 0, 'Debug mode']
            ];
            
            $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value, is_public, description) VALUES (?, ?, ?, ?)");
            foreach ($defaultSettings as $setting) {
                $stmt->execute($setting);
            }
            
            progress("Finalizing setup...");
            file_put_contents(DB_DIR . '/.setup_complete', date('Y-m-d H:i:s'));
            
            progress("<strong style='color:#0af'>Setup completed successfully!</strong>");
            echo '<script>setTimeout(() => window.location.href = "/dashboard/", 2000);</script>';
            
        } catch (Exception $e) {
            progress("<strong style='color:#f00'>Error: " . htmlspecialchars($e->getMessage()) . "</strong>");
            progress("Please check permissions and try again");
        }
        ?>
    </div>
</body>
</html>