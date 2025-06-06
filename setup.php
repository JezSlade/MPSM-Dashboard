<?php
// setup.php

// Start output buffering
ob_start();

// Include config.php to define SERVER_ROOT_PATH
require_once __DIR__ . '/config.php';

// Include db.php to ensure database connection is available
require_once SERVER_ROOT_PATH . 'db.php';

function execute_query($db, $sql) {
    if (!$db->query($sql)) {
        error_log("Query failed: " . $db->error . " (SQL: $sql)");
        return false;
    }
    return true;
}

// Initialize database connection
$db = connect_db();
if (!$db) {
    die("Database connection failed for setup.");
}


// Drop tables if reset is requested
if (isset($_GET['reset'])) {
    $tables = ['user_permissions', 'user_roles', 'role_permissions', 'permissions', 'users', 'roles', 'modules'];
    foreach ($tables as $table) {
        $db->query("DROP TABLE IF EXISTS $table");
    }
}

// Create tables
$queries = [
    "CREATE TABLE IF NOT EXISTS roles (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) UNIQUE NOT NULL)",
    "CREATE TABLE IF NOT EXISTS permissions (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) UNIQUE NOT NULL)",
    "CREATE TABLE IF NOT EXISTS modules (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, active TINYINT(1) DEFAULT 1)",
    "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role_id INT)",
    "CREATE TABLE IF NOT EXISTS user_roles (user_id INT, role_id INT, PRIMARY KEY (user_id, role_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS role_permissions (role_id INT, permission_id INT, PRIMARY KEY (role_id, permission_id), FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE, FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE)",
    "CREATE TABLE IF NOT EXISTS user_permissions (user_id INT, permission_id INT, PRIMARY KEY (user_id, permission_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE)"
];

foreach ($queries as $query) {
    execute_query($db, $query);
}

function table_exists($db, $table_name) {
    $result = $db->query("SHOW TABLES LIKE '$table_name'");
    return $result->num_rows > 0;
}
// Seed initial data
// Roles
execute_query($db, "INSERT IGNORE INTO roles (id, name) VALUES (1, 'Developer'), (2, 'Admin'), (3, 'Service'), (4, 'Sales'), (5, 'Guest')");

// Modules
execute_query($db, "INSERT IGNORE INTO modules (name, active) VALUES ('dashboard', 1), ('customers', 1), ('devices', 1), ('permissions', 1), ('devtools', 1), ('status', 1)");

// Default users
$default_password = password_hash('password', PASSWORD_DEFAULT);
$testuser_password = password_hash('test', PASSWORD_DEFAULT);

execute_query($db, "INSERT IGNORE INTO users (id, username, password, role_id) VALUES (\r\n    1, \r\n    'admin', \r\n    '" . $db->real_escape_string($default_password) . "', \r\n    (SELECT id FROM roles WHERE name = 'Admin')\r\n)");

execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'admin'), \r\n    (SELECT id FROM roles WHERE name = 'Admin')\r\n)");

execute_query($db, "INSERT IGNORE INTO users (id, username, password, role_id) VALUES (\r\n    2, \r\n    'dev', \r\n    '" . $db->real_escape_string($default_password) . "', \r\n    (SELECT id FROM roles WHERE name = 'Developer')\r\n)");

execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'dev'), \r\n    (SELECT id FROM roles WHERE name = 'Developer')\r\n)");

execute_query($db, "INSERT IGNORE INTO users (id, username, password, role_id) VALUES (\r\n    3, \r\n    'testuser', \r\n    '\" . $db->real_escape_string($testuser_password) . \"', \r\n    (SELECT id FROM roles WHERE name = 'Service')\r\n)\");\r\n\r\nexecute_query($db, \"INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'testuser'), \r\n    (SELECT id FROM roles WHERE name = 'Service')\r\n)\");\r\n\r\nexecute_query($db, \"INSERT IGNORE INTO permissions (name) VALUES \r\n    ('view_dashboard'), ('view_customers'), ('view_devices'), \r\n    ('manage_permissions'), ('custom_access'), ('view_devtools'), ('view_status')\r\n\");\r\n\r\n// Role permissions\r\nexecute_query($db, \"INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES \r\n    (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),  -- Developer\r\n    (2,1),(2,2),(2,3),(2,4),                    -- Admin\r\n    (3,3),                                      -- Service\r\n    (4,2),                                      -- Sales\r\n    (5,1)                                       -- Guest\r\n\");\r\n\r\nexecute_query($db, \"INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'testuser'), \r\n    (SELECT id FROM permissions WHERE name = 'custom_access')\r\n)\");\r\n\r\n$db->close();\r\n\r\n// Redirect to index after setup, preventing re-execution on refresh\r\nheader('Location: ' . WEB_ROOT_PATH . 'index.php');\r\nob_end_flush();\r\nexit;\r\n```

---

### **5. Modified File: `modules/status.php`**

It now includes `config.php` and then `db.php` and `functions.php` to ensure paths and necessary functions are available.

```php
<?php
// modules/status.php

// Include config.php to define SERVER_ROOT_PATH
require_once SERVER_ROOT_PATH . 'config.php';

// These includes are kept as per your original file.
// Ensure db.php and functions.php exist in your SERVER_ROOT_PATH.
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';

// Variables from your original status.php
// Ensure session_start() is called early in your index.php or a global config file
// if you intend to use $_SESSION['username'].
$username = $_SESSION['username'] ?? 'Unknown';
// Assuming $db is a global variable or the result of your db.php connection.
global $db; // Ensure $db is accessible here
$db_status = $db ? 'Connected' : 'Disconnected';

// $role and $accessible_modules are passed from index.php's scope
// (or ensure they are globally available if status.php is included standalone)
$current_user_role = $role ?? 'N/A'; // Use $role from index.php
$num_accessible_modules = count($accessible_modules ?? []);

?>

<div class="glass p-4 rounded-lg mt-4 text-sm">
    <h3 class="text-xl text-cyan-neon flex items-center mb-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        System Status
    </h3>
    <p class="flex items-center text-default mb-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: <?php echo $db_status === 'Connected' ? '#10B981' : '#EF4444'; ?>;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0v10"></path>
            </svg>
            Database: <span class="font-semibold ml-1"><?php echo $db_status; ?></span>
        </p>
        <p class="flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1h-1.25M15 10l4-4m-4 4l-4-4m4 4v7a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-4.5"></path>
            </svg>
            User: <span class="font-semibold ml-1"><?php echo htmlspecialchars($username); ?></span> (Role: <span class="font-semibold"><?php echo htmlspecialchars($current_user_role); ?></span>)
        </p>
        <p class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9a2 2 0 00-2-2h-7a2 2 0 00-2 2v10a2 2 0 002 2zM5 19h4m-9-2h9m-9-4h9m-9-4h9m-9-4h9m-9-2h2m-2 4h2m-2 4h2m-2 4h2"></path></svg>
            Accessible Modules: <span class="font-semibold ml-1"><?php echo $num_accessible_modules; ?></span>
        </p>
    </div>