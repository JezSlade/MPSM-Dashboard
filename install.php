<?php
/**
 * Installation script for MPSM Dashboard
 */

// Check if already installed
if (file_exists('.env') && file_exists('config/installed.txt')) {
    echo "MPSM Dashboard is already installed. If you want to reinstall, please delete the .env file and config/installed.txt file first.";
    exit;
}

// Database configuration
$db_host = 'localhost';
$db_name = 'mpsm_dashboard';
$db_user = 'mpsm_user';
$db_pass = 'mpsm_password';

// Check if .env file exists and load it
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    $db_host = $env['DB_HOST'] ?? $db_host;
    $db_name = $env['DB_NAME'] ?? $db_name;
    $db_user = $env['DB_USER'] ?? $db_user;
    $db_pass = $env['DB_PASSWORD'] ?? $db_pass;
}

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $db_host = $_POST['db_host'] ?? $db_host;
    $db_name = $_POST['db_name'] ?? $db_name;
    $db_user = $_POST['db_user'] ?? $db_user;
    $db_pass = $_POST['db_pass'] ?? $db_pass;
    $api_client_id = $_POST['api_client_id'] ?? '';
    $api_client_secret = $_POST['api_client_secret'] ?? '';
    $api_username = $_POST['api_username'] ?? '';
    $api_password = $_POST['api_password'] ?? '';
    $api_scope = $_POST['api_scope'] ?? '';
    $api_token_url = $_POST['api_token_url'] ?? '';
    $api_base_url = $_POST['api_base_url'] ?? '';
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_password = $_POST['admin_password'] ?? 'admin';
    
    // Validate required fields
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($api_client_id) || empty($api_client_secret) || empty($api_token_url) || empty($api_base_url)) {
        $message = "Please fill in all required fields.";
    } else {
        // Try to connect to database
        try {
            $db = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $db->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $db->exec("USE `$db_name`");
            
            // Create .env file
            $env_content = "DB_HOST=$db_host\n";
            $env_content .= "DB_NAME=$db_name\n";
            $env_content .= "DB_USER=$db_user\n";
            $env_content .= "DB_PASSWORD=$db_pass\n";
            $env_content .= "API_CLIENT_ID=$api_client_id\n";
            $env_content .= "API_CLIENT_SECRET=$api_client_secret\n";
            $env_content .= "API_USERNAME=$api_username\n";
            $env_content .= "API_PASSWORD=$api_password\n";
            $env_content .= "API_SCOPE=$api_scope\n";
            $env_content .= "API_TOKEN_URL=$api_token_url\n";
            $env_content .= "API_BASE_URL=$api_base_url\n";
            $env_content .= "SITE_NAME=MPSM Dashboard\n";
            $env_content .= "SITE_URL=http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "\n";
            
            file_put_contents('.env', $env_content);
            
            // Include config to initialize database connection
            require_once 'core/config.php';
            
            // Create tables
            createTables($db);
            
            // Create default users
            createDefaultUsers($db, $admin_username, $admin_password);
            
            // Register default widgets
            registerDefaultWidgets();
            
            // Create necessary directories
            createDirectories();
            
            // Mark as installed
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
            
            // Success message
            $message = "Installation completed successfully! <a href='login.php'>Go to login page</a>";
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

/**
 * Create database tables
 * @param PDO $db
 */
function createTables($db) {
    // Create users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            role VARCHAR(20) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create API tokens table
    $db->exec("
        CREATE TABLE IF NOT EXISTS api_tokens (
            id INT PRIMARY KEY,
            access_token TEXT NOT NULL,
            refresh_token TEXT,
            token_expiry INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create widgets table
    $db->exec("
        CREATE TABLE IF NOT EXISTS widgets (
            widget_id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            type VARCHAR(20) NOT NULL,
            class_name VARCHAR(100) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            config TEXT,
            required_permissions VARCHAR(255),
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create user widget permissions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_widget_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            widget_id VARCHAR(50) NOT NULL,
            can_view TINYINT(1) DEFAULT 1,
            can_edit TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (widget_id) REFERENCES widgets(widget_id) ON DELETE CASCADE,
            UNIQUE KEY user_widget (user_id, widget_id)
        )
    ");
    
    // Create user preferences table
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_preferences (
            user_id INT PRIMARY KEY,
            layout TEXT,
            theme VARCHAR(20) DEFAULT 'light',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create widget data table
    $db->exec("
        CREATE TABLE IF NOT EXISTS widget_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            widget_id VARCHAR(50) NOT NULL,
            data_key VARCHAR(100) NOT NULL,
            data_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (widget_id) REFERENCES widgets(widget_id) ON DELETE CASCADE,
            UNIQUE KEY widget_data_key (widget_id, data_key)
        )
    ");
}

/**
 * Create default users
 * @param PDO $db
 * @param string $admin_username
 * @param string $admin_password
 */
function createDefaultUsers($db, $admin_username, $admin_password) {
    // Create admin user
    $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin') ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
    $stmt->execute([$admin_username, $admin_password_hash]);
    
    // Create developer user
    $developer_password_hash = password_hash('developer', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'developer') ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
    $stmt->execute(['developer', $developer_password_hash]);
}

/**
 * Register default widgets
 */
function registerDefaultWidgets() {
    require_once 'core/widget_registry.php';
    
    $widget_registry = WidgetRegistry::getInstance();
    
    // Account Profile Widget
    $widget_registry->register_widget(
        'account_profile',
        'Account Profile',
        'Displays the user\'s account profile information',
        'api',
        'AccountProfileWidget',
        'widgets/account_profile.php',
        [
            'title' => 'Account Profile',
            'endpoint_id' => '/Account/GetProfile',
            'method' => 'get'
        ],
        ''
    );
    
    // Account Balance Widget
    $widget_registry->register_widget(
        'account_balance',
        'Account Balance',
        'Displays the user\'s account balance',
        'api',
        'AccountBalanceWidget',
        'widgets/account_balance.php',
        [
            'title' => 'Account Balance',
            'endpoint_id' => '/Account/GetBalance',
            'method' => 'get'
        ],
        ''
    );
    
    // Recent Transactions Widget
    $widget_registry->register_widget(
        'recent_transactions',
        'Recent Transactions',
        'Displays the user\'s recent transactions',
        'api',
        'RecentTransactionsWidget',
        'widgets/recent_transactions.php',
        [
            'title' => 'Recent Transactions',
            'endpoint_id' => '/Transactions/GetRecent',
            'method' => 'get',
            'params' => ['limit' => 5]
        ],
        ''
    );
    
    // Date & Time Widget
    $widget_registry->register_widget(
        'date_time',
        'Date & Time',
        'Displays the current date and time',
        'static',
        'DateTimeWidget',
        'widgets/date_time.php',
        [
            'title' => 'Date & Time',
            'format' => 'F j, Y g:i A'
        ],
        ''
    );
    
    // Add default permissions for admin user
    global $db;
    $admin_id = 1; // Assuming admin user has ID 1
    $widgets = ['account_profile', 'account_balance', 'recent_transactions', 'date_time'];
    
    foreach ($widgets as $widget_id) {
        $stmt = $db->prepare("
            INSERT INTO user_widget_permissions (user_id, widget_id, can_view, can_edit) 
            VALUES (?, ?, 1, 1)
            ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1
        ");
        $stmt->execute([$admin_id, $widget_id]);
    }
    
    // Add default layout for admin user
    $layout = json_encode([
        'dashboard' => $widgets
    ]);
    
    $stmt = $db->prepare("
        INSERT INTO user_preferences (user_id, layout) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE layout = VALUES(layout)
    ");
    $stmt->execute([$admin_id, $layout]);
}

/**
 * Create necessary directories
 */
function createDirectories() {
    $directories = [
        'widgets',
        'widgets/types',
        'core',
        'ajax',
        'config',
        'assets',
        'assets/css',
        'assets/js',
        'assets/img',
        'logs'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Install MPSM Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --text-color: #333;
            --bg-color: #f5f7fa;
            --card-bg: #ffffff;
            --border-color: #e1e5eb;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 30px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius-md);
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            color: #2e7d32;
        }
        
        .message a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }
        
        .message a:hover {
            text-decoration: underline;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: var(--primary-hover);
        }
        
        .section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        h2 {
            margin-bottom: 15px;
            font-size: 20px;
            color: var(--primary-color);
        }
        
        small {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>MPSM Dashboard Installation</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="section">
                <h2>Database Configuration</h2>
                <div class="form-group">
                    <label for="db_host">Database Host <span class="required">*</span></label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name <span class="required">*</span></label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Database User <span class="required">*</span></label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
                </div>
            </div>
            
            <div class="section">
                <h2>API Configuration</h2>
                <div class="form-group">
                    <label for="api_client_id">API Client ID <span class="required">*</span></label>
                    <input type="text" id="api_client_id" name="api_client_id" required>
                </div>
                
                <div class="form-group">
                    <label for="api_client_secret">API Client Secret <span class="required">*</span></label>
                    <input type="password" id="api_client_secret" name="api_client_secret" required>
                </div>
                
                <div class="form-group">
                    <label for="api_username">API Username</label>
                    <input type="text" id="api_username" name="api_username">
                </div>
                
                <div class="form-group">
                    <label for="api_password">API Password</label>
                    <input type="password" id="api_password" name="api_password">
                </div>
                
                <div class="form-group">
                    <label for="api_scope">API Scope</label>
                    <input type="text" id="api_scope" name="api_scope">
                </div>
                
                <div class="form-group">
                    <label for="api_token_url">API Token URL <span class="required">*</span></label>
                    <input type="text" id="api_token_url" name="api_token_url" required>
                </div>
                
                <div class="form-group">
                    <label for="api_base_url">API Base URL <span class="required">*</span></label>
                    <input type="text" id="api_base_url" name="api_base_url" required>
                </div>
            </div>
            
            <div class="section">
                <h2>Admin Account</h2>
                <div class="form-group">
                    <label for="admin_username">Admin Username</label>
                    <input type="text" id="admin_username" name="admin_username" value="admin">
                    <small>Default: admin</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" value="admin">
                    <small>Default: admin</small>
                </div>
            </div>
            
            <button type="submit">Install MPSM Dashboard</button>
        </form>
    </div>
</body>
</html>
