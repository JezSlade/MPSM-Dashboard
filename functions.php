<?php
// functions.php

// Use SERVER_ROOT_PATH from config.php
require_once SERVER_ROOT_PATH . 'db.php';

function get_permissions_for_role($role_id) {
    global $db;
    if (!$db) {
        error_log("Database connection is null in get_permissions_for_role.");
        return [];
    }
    $stmt = $db->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?");
    $stmt->bind_param('i', $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }
    return $permissions;
}

function get_user_permissions($user_id) {
    global $db;
    if (!$db) {
        error_log("Database connection is null in get_user_permissions.");
        return [];
    }
    if (!isset($_SESSION['role'])) {
        error_log("No role set in session for user_id: $user_id");
        return [];
    }

    $permissions = [];
    $role_name = $_SESSION['role'];

    // Get role ID based on role name
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->bind_param('s', $role_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc();

    if ($role) {
        $role_id = $role['id'];
        // Get permissions from the user's role
        $permissions = get_permissions_for_role($role_id);
    } else {
        error_log("Role not found: $role_name");
    }

    // Get custom permissions assigned directly to the user
    $stmt = $db->prepare("SELECT p.name FROM permissions p JOIN user_permissions up ON p.id = up.permission_id WHERE up.user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }

    return array_unique($permissions); // Remove duplicates
}

// Function to check if a user has a specific permission
function has_permission($permission_name) {
    // For now, always grant access to the 'Developer' role.
    // This allows developers to see all modules and manage permissions.
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Developer') {
        return true;
    }

    // If session user_id is not set, the user is not logged in.
    // Guest role handling is done via assigned modules in index.php or login logic.
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Get user's permissions
    $user_permissions = get_user_permissions($_SESSION['user_id']);

    // Check if the requested permission exists in the user's permissions
    return in_array($permission_name, $user_permissions);
}

// Function to fetch accessible modules for a user based on their role and custom permissions
function get_accessible_modules($role_id, $user_id) {
    global $db;
    if (!$db) {
        error_log("Database connection is null in get_accessible_modules.");
        return [];
    }

    $accessible_modules = [];

    // Get role-based permissions
    $role_permissions = get_permissions_for_role($role_id);
    foreach ($role_permissions as $permission) {
        // Map permissions to modules. This is a simplified mapping.
        // You might need a more sophisticated mapping or a 'module_permissions' table.
        switch ($permission) {
            case 'view_dashboard':
                $accessible_modules['Dashboard'] = SERVER_ROOT_PATH . 'modules/dashboard.php';
                break;
            case 'view_customers':
                $accessible_modules['Customers'] = SERVER_ROOT_PATH . 'modules/customers.php';
                break;
            case 'view_devices':
                $accessible_modules['Devices'] = SERVER_ROOT_PATH . 'modules/devices.php';
                break;
            case 'manage_permissions':
                $accessible_modules['Permissions'] = SERVER_ROOT_PATH . 'modules/permissions.php';
                break;
            case 'view_devtools':
                $accessible_modules['Dev Tools'] = SERVER_ROOT_PATH . 'modules/devtools.php';
                break;
            case 'view_status':
                $accessible_modules['Status'] = SERVER_ROOT_PATH . 'modules/status.php';
                break;
            // Add other module mappings here
        }
    }

    // Get custom user-specific permissions
    $custom_user_permissions = get_user_permissions($user_id);
    foreach ($custom_user_permissions as $permission) {
        // Re-map custom permissions to modules.
        // This ensures custom permissions can also grant module access.
        switch ($permission) {
            case 'view_dashboard':
                $accessible_modules['Dashboard'] = SERVER_ROOT_PATH . 'modules/dashboard.php';
                break;
            case 'view_customers':
                $accessible_modules['Customers'] = SERVER_ROOT_PATH . 'modules/customers.php';
                break;
            case 'view_devices':
                $accessible_modules['Devices'] = SERVER_ROOT_PATH . 'modules/devices.php';
                break;
            case 'manage_permissions':
                $accessible_modules['Permissions'] = SERVER_ROOT_PATH . 'modules/permissions.php';
                break;
            case 'view_devtools':
                $accessible_modules['Dev Tools'] = SERVER_ROOT_PATH . 'modules/devtools.php';
                break;
            case 'view_status':
                $accessible_modules['Status'] = SERVER_ROOT_PATH . 'modules/status.php';
                break;
            // Add other module mappings here for custom permissions
        }
    }

    // Remove duplicates and return
    return array_unique($accessible_modules);
}

// Ensure the database connection is available for other functions that might need it
global $db;
if (!isset($db) || $db === null) {
    $db = connect_db(); // Call connect_db only if $db is not already set
}