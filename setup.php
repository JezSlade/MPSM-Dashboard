<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'db.php';

// Create tables
$db->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role_id INT
)");
$db->query("CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE
)");
$db->query("CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE
)");
$db->query("CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id)
)");

// Seed roles
$roles = ['Developer', 'Admin', 'Dealer', 'Service', 'Sales', 'Guest'];
foreach ($roles as $role) {
    $db->query("INSERT IGNORE INTO roles (name) VALUES ('$role')");
}

// Seed admin user
$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
$db->query("INSERT IGNORE INTO users (username, password, role_id) VALUES ('admin', '$hashed_password', 1)");

// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions'];
foreach ($permissions as $perm) {
    $db->query("INSERT IGNORE INTO permissions (name) VALUES ('$perm')");
}

// Assign permissions to Developer role (id=1)
$developer_perms = [1, 2, 3, 4]; // Assuming permission IDs 1-4
foreach ($developer_perms as $perm_id) {
    $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, $perm_id)");
}

// Example permissions for other roles (adjust as needed)
$db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (2, 1), (2, 2), (2, 3)"); // Admin
$db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (3, 2), (3, 3)"); // Dealer
$db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (4, 3)"); // Service
$db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (5, 2)"); // Sales
$db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (6, 1)"); // Guest

echo "Database setup complete.";
?>
