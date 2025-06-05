<?php
ob_start(); // Buffer output to avoid header issues
require_once BASE_PATH . 'db.php';

// Function to execute SQL with error checking
function execute_query($db, $sql) {
    if (!$db->query($sql)) {
        error_log("Query failed: " . $db->error . " (SQL: $sql)");
        return false;
    }
    echo "Query executed successfully: $sql<br>";
    return true;
}

// Create tables
execute_query($db, "CREATE TABLE IF NOT EXISTS modules (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, active TINYINT(1) DEFAULT 1)");
execute_query($db, "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role_id INT, FOREIGN KEY (role_id) REFERENCES roles(id))");
execute_query($db, "CREATE TABLE IF NOT EXISTS roles (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) UNIQUE NOT NULL)");
execute_query($db, "CREATE TABLE IF NOT EXISTS permissions (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) UNIQUE NOT NULL)");
execute_query($db, "CREATE TABLE IF NOT EXISTS role_permissions (role_id INT, permission_id INT, PRIMARY KEY (role_id, permission_id), FOREIGN KEY (role_id) REFERENCES roles(id), FOREIGN KEY (permission_id) REFERENCES permissions(id))");
execute_query($db, "CREATE TABLE IF NOT EXISTS user_roles (user_id INT, role_id INT, PRIMARY KEY (user_id, role_id), FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (role_id) REFERENCES roles(id))");
execute_query($db, "CREATE TABLE IF NOT EXISTS user_permissions (user_id INT, permission_id INT, PRIMARY KEY (user_id, permission_id), FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (permission_id) REFERENCES permissions(id))");

// Seed roles
$roles = ['Developer', 'Admin', 'Service', 'Sales', 'Guest'];
foreach ($roles as $role) {
    $escaped_role = $db->real_escape_string($role);
    execute_query($db, "INSERT IGNORE INTO roles (name) VALUES ('$escaped_role')");
}

// Seed users with hashed passwords from your output
execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('admin', '$2y$12$USJhMo47DHHIhiYVLD12we8AHT1qYGxzoLlnVqH2iZl6K/OLMv/w2', (SELECT id FROM roles WHERE name = 'Admin'))");
execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'admin'), (SELECT id FROM roles WHERE name = 'Admin'))");
execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('testuser', '$2y$12$o2cNILBvip7suDfhNHV81.4KD3vQW7aP8cTvTvELt4OsPBoH/D.N2', (SELECT id FROM roles WHERE name = 'Service'))");
execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM roles WHERE name = 'Service'))");

// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access', 'view_devtools', 'view_status'];
foreach ($permissions as $perm) {
    $escaped_perm = $db->real_escape_string($perm);
    execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES ('$escaped_perm')");
}

// Seed role_permissions
$role_permissions = [
    [1, 1], [1, 2], [1, 3], [1, 4], [1, 5], [1, 6], [1, 7], // Developer
    [2, 1], [2, 2], [2, 3], [2, 4], // Admin
    [3, 3], // Service
    [4, 2], // Sales
    [5, 1]  // Guest
];
foreach ($role_permissions as $rp) {
    execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (" . implode(', ', $rp) . ")");
}

// Seed user_permissions
execute_query($db, "INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM permissions WHERE name = 'custom_access'))");

// Seed modules
$modules = ['dashboard', 'customers', 'devices', 'permissions', 'devtools'];
foreach ($modules as $module) {
    $escaped_module = $db->real_escape_string($module);
    execute_query($db, "INSERT IGNORE INTO modules (name, active) VALUES ('$escaped_module', 1)");
}

echo "<p class='text-green-500 p-4'>Database setup complete with hashed passwords. Setup will not run again.</p>";
ob_end_flush(); // Flush buffered output
?>