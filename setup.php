<?php
// Define BASE_PATH in the entry point
define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require_once BASE_PATH . 'db.php';

// Function to execute SQL with error checking
function execute_query($db, $sql) {
    if (!$db->query($sql)) {
        die("Query failed: " . $db->error . " (SQL: $sql)");
    }
    echo "Query executed successfully: $sql<br>";
}

// Create tables
execute_query($db, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL
)");
execute_query($db, "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
)");
execute_query($db, "CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
)");
execute_query($db, "CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id)
)");
execute_query($db, "CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id)
)");
execute_query($db, "CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT,
    permission_id INT,
    PRIMARY KEY (user_id, permission_id)
)");

// Seed roles
$roles = ['Developer', 'Admin', 'Service', 'Sales', 'Guest'];
foreach ($roles as $role) {
    execute_query($db, "INSERT IGNORE INTO roles (name) VALUES ('$role')");
}

// Seed admin user with plain text password
$plain_password = 'admin123';
execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('admin', '$plain_password', 1)");
execute_query($db, "INSERT INTO user_roles (user_id, role_id) VALUES (1, 1)");

// Seed test user
$plain_password = 'user123';
execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('testuser', '$plain_password', 3)");
execute_query($db, "INSERT INTO user_roles (user_id, role_id) VALUES (2, 3)");

// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access'];
foreach ($permissions as $perm) {
    execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES ('$perm')");
}

// Assign permissions to roles
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5)"); // Developer: all
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (2, 1), (2, 2), (2, 3)"); // Admin
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (3, 3)"); // Service
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (4, 2)"); // Sales
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (5, 1)"); // Guest

// Seed custom permission for test user
execute_query($db, "INSERT INTO user_permissions (user_id, permission_id) VALUES (2, 5)"); // Test user gets custom_access
// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access', 'view_devtools'];
foreach ($permissions as $perm) {
    execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES ('$perm')");
}

// Assign permissions to roles
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6)"); // Developer: all + view_devtools
execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (2, 1), (2, 2), (2, 3)"); // Admin

echo "Database setup complete with plain text password.";
?>