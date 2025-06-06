<?php
// functions.php
// The global $db variable will be available from index.php's scope,
// so no need to require_once db.php again here.

function get_permissions_for_role($role_id) {
    global $db;
    if (!$db) {
        // Changed error_log to echo for debugging
        echo "<p style='color: yellow;'>DEBUG (get_permissions_for_role): Database connection is null.</p>";
        return [];
    }
    $stmt = $db->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?");
    if (!$stmt) { // Add check for statement preparation
        echo "<p style='color: yellow;'>DEBUG (get_permissions_for_role): Failed to prepare statement: " . htmlspecialchars($db->error) . "</p>";
        return [];
    }
    $stmt->bind_param('i', $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }
    $stmt->close();
    return $permissions;
}

function get_user_permissions($user_id) {
    global $db;
    if (!$db) {
        // Changed error_log to echo for debugging
        echo "<p style='color: yellow;'>DEBUG (get_user_permissions): Database connection is null.</p>";
        return [];
    }
    if (!isset($_SESSION['role'])) {
        // Changed error_log to echo for debugging
        echo "<p style='color: yellow;'>DEBUG (get_user_permissions): No role set in session for user_id: " . htmlspecialchars($user_id) . "</p>";
        return [];
    }

    $permissions = [];
    $role_name = $_SESSION['role'];

    // Get role ID based on role name
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    if (!$stmt) { // Add check for statement preparation
        echo "<p style='color: yellow;'>DEBUG (get_user_permissions): Failed to prepare statement for role ID: " . htmlspecialchars($db->error) . "</p>";
        return [];
    }
    $stmt->bind_param('s', $role_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc();

    if ($role) {
        $role_id = $role['id'];
        // Get permissions from the user's role
        $permissions = get_permissions_for_role($role_id);
    } else {
        // Changed error_log to echo for debugging
        echo "<p style='color: yellow;'>DEBUG (get_user_permissions): Role not found: " . htmlspecialchars($role_name) . "</p>";
    }
    $stmt->close();

    // Get custom permissions assigned directly to the user
    $stmt = $db->prepare("SELECT p.name FROM permissions p JOIN user_permissions up ON p.id = up.permission_id WHERE up.user_id = ?");
    if (!$stmt) { // Add check for statement preparation
        echo "<p style='color: yellow;'>DEBUG (get_user_permissions): Failed to prepare statement for user permissions: " . htmlspecialchars($db->error) . "</p>";
        return array_unique($permissions);
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }
    $stmt->close();

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
    echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Called with Role ID: " . htmlspecialchars($role_id) . ", User ID: " . htmlspecialchars($user_id) . "</p>"; // DEBUG
    if (!$db) {
        echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Database connection is null.</p>"; // DEBUG
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
        echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Failed to prepare statement for role modules: " . htmlspecialchars($db->error) . "</p>"; // DEBUG
        return [];
    }
    $stmt->bind_param('i', $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Role modules query num_rows: " . htmlspecialchars($result->num_rows) . "</p>"; // DEBUG
    while ($row = $result->fetch_assoc()) {
        $accessible_modules[ucfirst($row['name'])] = SERVER_ROOT_PATH . 'modules/' . $row['name'] . '.php';
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
        echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Failed to prepare statement for user modules: " . htmlspecialchars($db->error) . "</p>"; // DEBUG
        return array_unique($accessible_modules);
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): User modules query num_rows: " . htmlspecialchars($result->num_rows) . "</p>"; // DEBUG
    while ($row = $result->fetch_assoc()) {
        $accessible_modules[ucfirst($row['name'])] = SERVER_ROOT_PATH . 'modules/' . $row['name'] . '.php';
    }
    $stmt->close();

    // Sort accessible modules alphabetically for consistent display
    ksort($accessible_modules);

    echo "<p style='color: yellow;'>DEBUG (get_accessible_modules): Final accessible modules array: <pre>" . htmlspecialchars(print_r($accessible_modules, true)) . "</pre></p>"; // DEBUG
    return array_unique($accessible_modules);
}

// You might also want to add a function to check user permissions in general
function has_permission_for_user($user_id, $permission_name) {
    global $db;
    if (!$db) {
        echo "<p style='color: yellow;'>DEBUG (has_permission_for_user): Database connection is null.</p>"; // DEBUG
        return false;
    }
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM user_permissions up
        JOIN permissions p ON up.permission_id = p.id
        WHERE up.user_id = ? AND p.name = ?
    ");
    if (!$stmt) { // Add check for statement preparation
        echo "<p style='color: yellow;'>DEBUG (has_permission_for_user): Failed to prepare statement: " . htmlspecialchars($db->error) . "</p>";
        return false;
    }
    $stmt->bind_param('is', $user_id, $permission_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// table_exists() function should be in db.php, not here.

?>