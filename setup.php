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

execute_query($db, "INSERT IGNORE INTO users (id, username, password, role_id) VALUES (\r\n    3, \r\n    'testuser', \r\n    '" . $db->real_escape_string($testuser_password) . "', \r\n    (SELECT id FROM roles WHERE name = 'Service')\r\n)"); // <-- FIXED LINE

execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'testuser'), \r\n    (SELECT id FROM roles WHERE name = 'Service')\r\n)");

execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES \r\n    ('view_dashboard'), ('view_customers'), ('view_devices'), \r\n    ('manage_permissions'), ('custom_access'), ('view_devtools'), ('view_status')\r\n");

// Role permissions
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES \r\n    (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),  -- Developer\r\n    (2,1),(2,2),(2,3),(2,4),                    -- Admin\r\n    (3,3),                                      -- Service\r\n    (4,2),                                      -- Sales\r\n    (5,1)                                       -- Guest\r\n");

execute_query($db, "INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (\r\n    (SELECT id FROM users WHERE username = 'testuser'), \r\n    (SELECT id FROM permissions WHERE name = 'custom_access')\r\n)");

$db->close();

// Redirect to index after setup, preventing re-execution on refresh
header('Location: ' . WEB_ROOT_PATH . 'index.php');
ob_end_flush();
exit;