<?php
ob_start(); // Start output buffering
// Remove BASE_PATH definition, rely on parameter from index.php

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

// Create tables with foreign keys
if (!execute_query($db, "CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    active TINYINT(1) DEFAULT 1
)")) die("Failed to create modules table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
)")) die("Failed to create users table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
)")) die("Failed to create roles table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
)")) die("Failed to create permissions table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
)")) die("Failed to create role_permissions table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
)")) die("Failed to create user_roles table.");

if (!execute_query($db, "CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT,
    permission_id INT,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
)")) die("Failed to create user_permissions table.");

// Debug: Check database connection state
if (!$db) {
    die("Database connection lost before seeding.");
}

// Seed roles
$roles = ['Developer', 'Admin', 'Service', 'Sales', 'Guest'];
foreach ($roles as $role) {
    $escaped_role = $db->real_escape_string($role);
    if (!execute_query($db, "INSERT IGNORE INTO roles (name) VALUES ('$escaped_role')")) {
        error_log("Failed to insert role: $role");
    }
}

// Seed admin user with hashed password
$plain_password = 'admin123';
$hashed_password = $db->real_escape_string(password_hash($plain_password, PASSWORD_DEFAULT));
if (!execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('admin', '$hashed_password', (SELECT id FROM roles WHERE name = 'Admin'))")) {
    error_log("Failed to insert admin user");
}
if (!execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'admin'), (SELECT id FROM roles WHERE name = 'Admin'))")) {
    error_log("Failed to assign admin role");
}

// Seed test user with hashed password
$plain_password = 'user123';
$hashed_password = $db->real_escape_string(password_hash($plain_password, PASSWORD_DEFAULT));
if (!execute_query($db, "INSERT IGNORE INTO users (username, password, role_id) VALUES ('testuser', '$hashed_password', (SELECT id FROM roles WHERE name = 'Service'))")) {
    error_log("Failed to insert testuser");
}
if (!execute_query($db, "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM roles WHERE name = 'Service'))")) {
    error_log("Failed to assign testuser role");
}

// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access', 'view_devtools'];
foreach ($permissions as $perm) {
    $escaped_perm = $db->real_escape_string($perm);
    if (!execute_query($db, "INSERT IGNORE INTO permissions (name) VALUES ('$escaped_perm')")) {
        error_log("Failed to insert permission: $perm");
    }
}

// Assign permissions to roles
$role_permissions = [
    ['Developer', ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access', 'view_devtools']],
    ['Admin', ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions']],
    ['Service', ['view_devices']],
    ['Sales', ['view_customers']],
    ['Guest', ['view_dashboard']]
];
foreach ($role_permissions as $rp) {
    $role_name = $db->real_escape_string($rp[0]);
    $perms = $rp[1];
    $role_id_result = $db->query("SELECT id FROM roles WHERE name = '$role_name'");
    $role_id = $role_id_result->fetch_row()[0];
    $role_id_result->free();
    foreach ($perms as $perm) {
        $escaped_perm = $db->real_escape_string($perm);
        $perm_id_result = $db->query("SELECT id FROM permissions WHERE name = '$escaped_perm'");
        $perm_id = $perm_id_result->fetch_row()[0];
        $perm_id_result->free();
        if ($role_id && $perm_id) {
            if (!execute_query($db, "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ($role_id, $perm_id)")) {
                error_log("Failed to assign permission $perm to role $role_name");
            }
        }
    }
}

// Seed custom permission for test user
if (!execute_query($db, "INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM permissions WHERE name = 'custom_access'))")) {
    error_log("Failed to assign custom permission to testuser");
}

// Seed modules
$modules = ['dashboard', 'customers', 'devices', 'permissions', 'devtools'];
foreach ($modules as $module) {
    $escaped_module = $db->real_escape_string($module);
    if (!execute_query($db, "INSERT IGNORE INTO modules (name, active) VALUES ('$escaped_module', 1)")) {
        error_log("Failed to insert module: $module");
    }
}

echo "<p class='text-green-500 p-4'>Database setup complete with hashed passwords. Setup will not run again.</p>";
ob_end_flush(); // Flush the buffered output
?>