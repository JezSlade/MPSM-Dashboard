<?php
require_once BASE_PATH . 'db.php';

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

function has_permission($permission) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $user_permissions = $_SESSION['permissions'] ?? get_user_permissions($_SESSION['user_id']);
    $_SESSION['permissions'] = $user_permissions; // Cache permissions in session
    return in_array($permission, $user_permissions);
}
?>