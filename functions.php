<?php
// functions.php
// The global $db variable will be available from index.php's scope,
// so no need to require_once db.php again here.

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

/**
 * Checks if the current logged-in user has a specific permission.
 * Requires get_user_permissions() to be available.
 * @param string $permission_name The name of the permission to check (e.g., 'view_dashboard', 'manage_users').
 * @return bool True if the user has the permission, false otherwise.
 */
function has_permission($permission_name) {
    // Ensure $_SESSION['user_permissions'] is populated
    if (!isset($_SESSION['user_permissions']) || !is_array($_SESSION['user_permissions'])) {
        // If permissions aren't in session, attempt to load them (should happen on login)
        if (isset($_SESSION['user_id'])) {
            $_SESSION['user_permissions'] = get_user_permissions($_SESSION['user_id']);
        } else {
            return false; // No user logged in, no permissions
        }
    }
    return in_array($permission_name, $_SESSION['user_permissions']);
}

/**
 * Fetches a list of modules accessible by a given role and user, considering both role-based and user-specific permissions.
 *
 * @param int $role_id The ID of the user's role.
 * @param int $user_id The ID of the user.
 * @return array An associative array of accessible module names (keys) and their file paths (values).
 */
function get_accessible_modules($role_id, $user_id) {
    global $db;
    if (!$db) {
        error_log("Database connection is null in get_accessible_modules.");
        return [];
    }

    $accessible_modules = [];

    // Modules based on role permissions
    // Modified to include 'manage_permissions' for the 'permissions' module
    $stmt = $db->prepare("
        SELECT DISTINCT m.name
        FROM modules m
        JOIN permissions p ON p.name = CONCAT('view_', m.name) OR (m.name = 'permissions' AND p.name = 'manage_permissions')
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ? AND m.active = 1
    ");
    if (!$stmt) {
        error_log("Failed to prepare statement for role modules: " . $db->error);
        return [];
    }
    $stmt->bind_param('i', $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Corrected path: modules are in the root, not in a 'modules/' subfolder
        $accessible_modules[ucfirst($row['name'])] = SERVER_ROOT_PATH . $row['name'] . '.php';
    }
    $stmt->close();

    // Modules based on user-specific permissions (e.g., 'custom_access')
    // Also modified for 'manage_permissions' on the 'permissions' module
    $stmt = $db->prepare("
        SELECT DISTINCT m.name
        FROM modules m
        JOIN permissions p ON p.name = CONCAT('view_', m.name) OR p.name = 'custom_access' OR (m.name = 'permissions' AND p.name = 'manage_permissions')
        JOIN user_permissions up ON p.id = up.permission_id
        WHERE up.user_id = ? AND m.active = 1
    ");
    if (!$stmt) {
        error_log("Failed to prepare statement for user modules: " . $db->error);
        return array_unique($accessible_modules);
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Corrected path: modules are in the root, not in a 'modules/' subfolder
        $accessible_modules[ucfirst($row['name'])] = SERVER_ROOT_PATH . $row['name'] . '.php';
    }
    $stmt->close();

    // Sort accessible modules alphabetically for consistent display
    ksort($accessible_modules);

    return array_unique($accessible_modules);
}

// You might also want to add a function to check user permissions in general
function has_permission_for_user($user_id, $permission_name) {
    global $db;
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM user_permissions up
        JOIN permissions p ON up.permission_id = p.id
        WHERE up.user_id = ? AND p.name = ?
    ");
    $stmt->bind_param('is', $user_id, $permission_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// table_exists() function should be in db.php, not here.

?>