<?php
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

// Seed roles
$roles = ['Developer', 'Admin', 'Service', 'Sales', 'Guest'];
foreach ($roles as $role) {
    $stmt = $db->prepare("INSERT IGNORE INTO roles (name) VALUES (?)");
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $stmt->close();
}

// Seed admin user with hashed password
$plain_password = 'admin123';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT IGNORE INTO users (username, password, role_id) VALUES (?, ?, (SELECT id FROM roles WHERE name = 'Admin'))");
$stmt->bind_param('ss', 'admin', $hashed_password);
$stmt->execute();
$stmt->close();
$stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'admin'), (SELECT id FROM roles WHERE name = 'Admin'))");
$stmt->execute();
$stmt->close();

// Seed test user with hashed password
$plain_password = 'user123';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT IGNORE INTO users (username, password, role_id) VALUES (?, ?, (SELECT id FROM roles WHERE name = 'Service'))");
$stmt->bind_param('ss', 'testuser', $hashed_password);
$stmt->execute();
$stmt->close();
$stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM roles WHERE name = 'Service'))");
$stmt->execute();
$stmt->close();

// Seed permissions
$permissions = ['view_dashboard', 'view_customers', 'view_devices', 'manage_permissions', 'custom_access', 'view_devtools'];
foreach ($permissions as $perm) {
    $stmt = $db->prepare("INSERT IGNORE INTO permissions (name) VALUES (?)");
    $stmt->bind_param('s', $perm);
    $stmt->execute();
    $stmt->close();
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
    $role_name = $rp[0];
    $perms = $rp[1];
    $role_id_stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $role_id_stmt->bind_param('s', $role_name);
    $role_id_stmt->execute();
    $role_id = $role_id_stmt->get_result()->fetch_row()[0];
    $role_id_stmt->close();
    foreach ($perms as $perm) {
        $perm_id_stmt = $db->prepare("SELECT id FROM permissions WHERE name = ?");
        $perm_id_stmt->bind_param('s', $perm);
        $perm_id_stmt->execute();
        $perm_id = $perm_id_stmt->get_result()->fetch_row()[0];
        $perm_id_stmt->close();
        if ($role_id && $perm_id) {
            $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $role_id, $perm_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Seed custom permission for test user
$stmt = $db->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES ((SELECT id FROM users WHERE username = 'testuser'), (SELECT id FROM permissions WHERE name = 'custom_access'))");
$stmt->execute();
$stmt->close();

// Seed modules
$modules = ['dashboard', 'customers', 'devices', 'permissions', 'devtools'];
foreach ($modules as $module) {
    $stmt = $db->prepare("INSERT IGNORE INTO modules (name, active) VALUES (?, 1)");
    $stmt->bind_param('s', $module);
    $stmt->execute();
    $stmt->close();
}

echo "<p class='text-green-500 p-4'>Database setup complete with hashed passwords. Setup will not run again.</p>";
?>