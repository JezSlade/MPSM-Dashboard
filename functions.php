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
    $permissions = [];

    // Get permissions from all roles the user belongs to
    $result = $db->query("SELECT r.id FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = $user_id");
    if ($result !== false) {
        while ($role = $result->fetch_assoc()) {
            $permissions = array_merge($permissions, get_permissions_for_role($role['id']));
        }
    }

    // Get custom permissions assigned directly to the user
    $result = $db->query("SELECT p.name FROM permissions p JOIN user_permissions up ON p.id = up.permission_id WHERE up.user_id = $user_id");
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['name'];
        }
    }

    return array_unique($permissions); // Remove duplicates
}

function has_permission($permission) {
    if (!isset($_SESSION['user_id'])) return false;
    $user_permissions = $_SESSION['permissions'] ?? get_user_permissions($_SESSION['user_id']);
    $_SESSION['permissions'] = $user_permissions; // Cache permissions in session
    return in_array($permission, $user_permissions);
}
?>