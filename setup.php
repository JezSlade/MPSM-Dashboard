<?php
// Start output buffering
ob_start();

// Define BASE_PATH if not already defined
defined('BASE_PATH') or define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once BASE_PATH . 'db.php';

function execute_query($db, $sql) {
    if (!$db->query($sql)) {
        error_log("Query failed: " . $db->error . " (SQL: $sql)");
        return false;
    }
    return true;
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
    "CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INT, 
        permission_id INT, 
        PRIMARY KEY (role_id, permission_id), 
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id)
    )",
    "CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT, 
        role_id INT, 
        PRIMARY KEY (user_id, role_id), 
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )",
    "CREATE TABLE IF NOT EXISTS user_permissions (
        user_id INT, 
        permission_id INT, 
        PRIMARY KEY (user_id, permission_id), 
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id)
    )"
];

foreach ($queries as $query) {
    execute_query($db, $query);
}

// Seed data (same as before but with proper password escaping)
$admin_password = '$2y$12$USJhMo47DHHIhiYVLD12we8AHT1qYGxzoLlnVqH2iZl6K/OLMv/w2';
$testuser_password = '$2y$12$o2cNILBvip7suDfhNHV81.4KD3vQW7aP8cTvTvELt4OsPBoH/D.N2';

execute_query($db, "INSERT IGNORE INTO roles (name) VALUES ('Developer'), ('Admin'), ('Service'), ('Sales'), ('Guest')");

execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES (
    'admin', 
    '" . $db->real_escape_string($admin_password) . "', 
    (SELECT id FROM roles WHERE name = 'Admin')
)");

execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (
    (SELECT id FROM users WHERE username = 'admin'), 
    (SELECT id FROM roles WHERE name = 'Admin')
)");

execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES (
    'testuser', 
    '" . $db->real_escape_string($testuser_password) . "', 
    (SELECT id FROM roles WHERE name = 'Service')
)");

execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (
    (SELECT id FROM users WHERE username = 'testuser'), 
    (SELECT id FROM roles WHERE name = 'Service')
)");

execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES 
    ('view_dashboard'), ('view_customers'), ('view_devices'), 
    ('manage_permissions'), ('custom_access'), ('view_devtools'), ('view_status')
");

// Role permissions
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES 
    (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),  -- Developer
    (2,1),(2,2),(2,3),(2,4),                    -- Admin
    (3,3),                                      -- Service
    (4,2),                                      -- Sales
    (5,1)                                       -- Guest
");

execute_query($db, "INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (
    (SELECT id FROM users WHERE username = 'testuser'), 
    (SELECT id FROM permissions WHERE name = 'custom_access')
)");

execute_query($db, "INSERT IGNORE INTO modules (name, active) VALUES 
    ('dashboard', 1), ('customers', 1), ('devices', 1), 
    ('permissions', 1), ('devtools', 1)
");

// Clean output buffer and redirect
ob_end_clean();
header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
exit;
?>