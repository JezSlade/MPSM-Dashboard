<?php
// modules/permissions.php

// Include config.php to define SERVER_ROOT_PATH
require_once SERVER_ROOT_PATH . 'config.php';

// These includes are kept as per your original file.
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';

global $db; // Ensure $db is accessible here

if (!has_permission('manage_permissions')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}

function get_active_modules() {
    global $db;
    if (!$db) {
        error_log("Database connection is null in get_active_modules.");
        return [];
    }
    $result = $db->query("SELECT name FROM modules WHERE active = 1");
    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row['name'];
    }
    return $modules;
}

$active_modules = get_active_modules();
$action = $_GET['action'] ?? 'list';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_role'])) {
            $role_name = trim($_POST['role_name']);
            if (empty($role_name)) {
                throw new Exception("Role name is required.");
            }
            $stmt = $db->prepare("INSERT INTO roles (name) VALUES (?)");
            $stmt->bind_param('s', $role_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&success=Role added.");
            exit;
        } elseif (isset($_POST['add_permission'])) {
            $permission_name = trim($_POST['permission_name']);
            if (empty($permission_name)) {
                throw new Exception("Permission name is required.");
            }
            $stmt = $db->prepare("INSERT INTO permissions (name) VALUES (?)");
            $stmt->bind_param('s', $permission_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&success=Permission added.");
            exit;
        } elseif (isset($_POST['toggle_module_status'])) {
            $module_name = $_POST['module_name'];
            $new_status = $_POST['status'] == '1' ? 1 : 0;
            $stmt = $db->prepare("UPDATE modules SET active = ? WHERE name = ?");
            $stmt->bind_param('is', $new_status, $module_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&success=Module status updated.");
            exit;
        } elseif (isset($_POST['assign_role_permissions'])) {
            $role_id = $_POST['role_id'];
            $selected_permissions = $_POST['permissions'] ?? [];

            // Clear existing permissions for the role
            $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $stmt->close();

            // Insert new permissions
            if (!empty($selected_permissions)) {
                $insert_values = [];
                $params = [];
                $types = '';
                foreach ($selected_permissions as $perm_id) {
                    $insert_values[] = '(?, ?)';
                    $params[] = $role_id;
                    $params[] = $perm_id;
                    $types .= 'ii';
                }
                $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES " . implode(', ', $insert_values));
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
            }
            header("Location: index.php?module=permissions&success=Role permissions updated.");
            exit;
        } elseif (isset($_POST['assign_user_roles']) || isset($_POST['assign_custom_permissions'])) {
            $user_id_to_manage = $_POST['user_id'];

            if (isset($_POST['assign_user_roles'])) {
                $selected_roles = $_POST['user_roles'] ?? [];

                // Clear existing roles for the user
                $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
                $stmt->bind_param('i', $user_id_to_manage);
                $stmt->execute();
                $stmt->close();

                // Insert new roles
                if (!empty($selected_roles)) {
                    $insert_values = [];
                    $params = [];
                    $types = '';
                    foreach ($selected_roles as $role_id) {
                        $insert_values[] = '(?, ?)';
                        $params[] = $user_id_to_manage;
                        $params[] = $role_id;
                        $types .= 'ii';
                    }
                    $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES " . implode(', ', $insert_values));
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $stmt->close();

                    // Update primary role in users table (if applicable, e.g., to the first selected role)
                    if (!empty($selected_roles)) {
                        $primary_role_id = $selected_roles[0]; // Set the first role as primary
                        $stmt = $db->prepare("UPDATE users SET role_id = ? WHERE id = ?");
                        $stmt->bind_param('ii', $primary_role_id, $user_id_to_manage);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                header("Location: index.php?module=permissions&action=manage_users&user_id=" . $user_id_to_manage . "&success=User roles updated.");
                exit;

            } elseif (isset($_POST['assign_custom_permissions'])) {
                $selected_permissions = $_POST['custom_permissions'] ?? [];

                // Clear existing custom permissions for the user
                $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
                $stmt->bind_param('i', $user_id_to_manage);
                $stmt->execute();
                $stmt->close();

                // Insert new custom permissions
                if (!empty($selected_permissions)) {
                    $insert_values = [];
                    $params = [];
                    $types = '';
                    foreach ($selected_permissions as $perm_id) {
                        $insert_values[] = '(?, ?)';
                        $params[] = $user_id_to_manage;
                        $params[] = $perm_id;
                        $types .= 'ii';
                    }
                    $stmt = $db->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES " . implode(', ', $insert_values));
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $stmt->close();
                }
                header("Location: index.php?module=permissions&action=manage_users&user_id=" . $user_id_to_manage . "&success=Custom permissions updated.");
                exit;
            }
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        // Fallback to list view with error
        header("Location: index.php?module=permissions&error=" . urlencode($error_message));
        exit;
    }
}

// Fetch data for display
$roles = [];
$result = $db->query("SELECT id, name FROM roles ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

$permissions = [];
$result = $db->query("SELECT id, name FROM permissions ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $permissions[] = $row;
}

$modules = [];
$result = $db->query("SELECT id, name, active FROM modules ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}

// For Role Permissions Management
$role_permissions = []; // role_id => [permission_id1, permission_id2]
$result = $db->query("SELECT role_id, permission_id FROM role_permissions");
while ($row = $result->fetch_assoc()) {
    $role_permissions[$row['role_id']][] = $row['permission_id'];
}

// For User Management
$users = [];
$result = $db->query("SELECT u.id, u.username, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.username");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$available_roles = $roles; // All roles for user assignment
$available_permissions = $permissions; // All permissions for custom assignment

$user_to_manage = null;
if ($action === 'manage_users' && isset($_GET['user_id'])) {
    $user_id_param = $_GET['user_id'];
    $stmt = $db->prepare("SELECT id, username, role_id FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_to_manage = $result->fetch_assoc();

    if ($user_to_manage) {
        // Fetch roles assigned to this specific user
        $user_roles_ids = [];
        $stmt_roles = $db->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
        $stmt_roles->bind_param('i', $user_to_manage['id']);
        $stmt_roles->execute();
        $result_roles = $stmt_roles->get_result();
        while ($row_role = $result_roles->fetch_assoc()) {
            $user_roles_ids[] = $row_role['role_id'];
        }

        // Fetch custom permissions assigned to this specific user
        $user_perm_ids = [];
        $module_permissions = []; // To map permission name to module name
        $stmt_perms = $db->prepare("SELECT p.id, p.name FROM permissions p JOIN user_permissions up ON p.id = up.permission_id WHERE up.user_id = ?");
        $stmt_perms->bind_param('i', $user_to_manage['id']);
        $stmt_perms->execute();
        $result_perms = $stmt_perms->get_result();
        while ($row_perm = $result_perms->fetch_assoc()) {
            $user_perm_ids[] = $row_perm['id'];
            // Attempt to link permissions to modules for better context (optional)
            foreach ($active_modules as $module_name) {
                if (str_starts_with($row_perm['name'], 'view_' . $module_name)) {
                    $module_permissions[$row_perm['name']] = $module_name;
                    break;
                }
            }
        }
    }
}

$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';

?>

<div class="floating-module p-6">
    <h2 class="text-2xl text-cyan-neon mb-6">Permissions Management</h2>

    <?php if ($success_message): ?>
        <p class="text-green-500 mb-4"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="tabs mb-4">
        <a href="?module=permissions&action=list" class="tab-button <?php echo $action === 'list' ? 'active' : ''; ?>">Overview</a>
        <a href="?module=permissions&action=roles" class="tab-button <?php echo $action === 'roles' ? 'active' : ''; ?>">Manage Roles</a>
        <a href="?module=permissions&action=permissions" class="tab-button <?php echo $action === 'permissions' ? 'active' : ''; ?>">Manage Permissions</a>
        <a href="?module=permissions&action=modules" class="tab-button <?php echo $action === 'modules' ? 'active' : ''; ?>">Manage Modules</a>
        <a href="?module=permissions&action=manage_users" class="tab-button <?php echo $action === 'manage_users' ? 'active' : ''; ?>">Manage Users</a>
    </div>

    <?php if ($action === 'list'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Current Roles & Permissions Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="glass p-4 rounded-lg">
                <h4 class="font-semibold text-lg text-teal-custom mb-2">Roles</h4>
                <ul class="list-disc ml-5">
                    <?php foreach ($roles as $role): ?>
                        <li><?php echo htmlspecialchars($role['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="glass p-4 rounded-lg">
                <h4 class="font-semibold text-lg text-teal-custom mb-2">Permissions</h4>
                <ul class="list-disc ml-5">
                    <?php foreach ($permissions as $permission): ?>
                        <li><?php echo htmlspecialchars($permission['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="glass p-4 rounded-lg">
                <h4 class="font-semibold text-lg text-teal-custom mb-2">Active Modules</h4>
                <ul class="list-disc ml-5">
                    <?php foreach ($modules as $module): ?>
                        <?php if ($module['active']): ?>
                            <li><?php echo htmlspecialchars($module['name']); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    <?php elseif ($action === 'roles'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Roles and Role Permissions</h3>

        <div class="glass p-4 rounded-lg mb-6">
            <h4 class="font-semibold text-lg text-teal-custom mb-2">Add New Role</h4>
            <form method="POST" action="?module=permissions&action=roles" class="flex flex-col space-y-4">
                <input type="text" name="role_name" placeholder="New Role Name" required class="input-field">
                <button type="submit" name="add_role" class="btn btn-primary">Add Role</button>
            </form>
        </div>

        <div class="glass p-4 rounded-lg">
            <h4 class="font-semibold text-lg text-teal-custom mb-2">Assign Permissions to Roles</h4>
            <?php foreach ($roles as $role): ?>
                <div class="mb-4 p-3 rounded-lg bg-black-smoke">
                    <h5 class="font-semibold text-lg text-orange-neon mb-2"><?php echo htmlspecialchars($role['name']); ?></h5>
                    <form method="POST" action="?module=permissions&action=roles">
                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                        <select name="permissions[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                            <?php
                            $current_role_permissions = $role_permissions[$role['id']] ?? [];
                            foreach ($permissions as $permission):
                            ?>
                                <option value="<?php echo $permission['id']; ?>"
                                    <?php echo in_array($permission['id'], $current_role_permissions) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($permission['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="assign_role_permissions" class="btn btn-primary mt-3">Save Permissions</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($action === 'permissions'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Global Permissions</h3>
        <div class="glass p-4 rounded-lg mb-6">
            <h4 class="font-semibold text-lg text-teal-custom mb-2">Add New Permission</h4>
            <form method="POST" action="?module=permissions&action=permissions" class="flex flex-col space-y-4">
                <input type="text" name="permission_name" placeholder="New Permission Name (e.g., edit_users)" required class="input-field">
                <button type="submit" name="add_permission" class="btn btn-primary">Add Permission</button>
            </form>
        </div>
        <div class="glass p-4 rounded-lg">
            <h4 class="font-semibold text-lg text-teal-custom mb-2">Existing Permissions</h4>
            <ul class="list-disc ml-5">
                <?php foreach ($permissions as $permission): ?>
                    <li><?php echo htmlspecialchars($permission['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php elseif ($action === 'modules'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Module Visibility</h3>
        <div class="glass p-4 rounded-lg">
            <?php foreach ($modules as $module): ?>
                <div class="flex items-center justify-between p-2 mb-2 bg-black-smoke rounded">
                    <span><?php echo htmlspecialchars(ucwords($module['name'])); ?></span>
                    <form method="POST" action="?module=permissions&action=modules">
                        <input type="hidden" name="module_name" value="<?php echo htmlspecialchars($module['name']); ?>">
                        <select name="status" onchange="this.form.submit()" class="select-field">
                            <option value="1" <?php echo $module['active'] ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo !$module['active'] ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <noscript><button type="submit" name="toggle_module_status" class="btn btn-primary ml-2">Update</button></noscript>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($action === 'manage_users'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage User Roles & Custom Permissions</h3>

        <div class="glass p-4 rounded-lg mb-6">
            <h4 class="font-semibold text-lg text-teal-custom mb-2">Select User to Manage</h4>
            <form method="GET" action="">
                <input type="hidden" name="module" value="permissions">
                <input type="hidden" name="action" value="manage_users">
                <select name="user_id" onchange="this.form.submit()" class="select-field">
                    <option value="">-- Select a User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($user_to_manage['id'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($user_to_manage): ?>
            <div class="glass p-4 rounded-lg">
                <h4 class="font-semibold text-lg text-orange-neon mb-4">Managing Permissions for: <?php echo htmlspecialchars($user_to_manage['username']); ?></h4>

                <form method="POST" action="?module=permissions&action=manage_users&user_id=<?php echo $user_to_manage['id']; ?>" class="space-y-6">
                    <input type="hidden" name="user_id" value="<?php echo $user_to_manage['id']; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-1">Assigned Roles:</label>
                            <select name="user_roles[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                                <?php foreach ($available_roles as $role_option): ?>
                                    <option value="<?php echo $role_option['id']; ?>" <?php echo in_array($role_option['id'], $user_roles_ids) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role_option['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-1">Custom Permissions:</label>
                            <select name="custom_permissions[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                                <?php foreach ($available_permissions as $perm): ?>
                                    <option value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $user_perm_ids) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($perm['name']); ?> (Module: <?php echo $module_permissions[$perm['name']] ?? 'N/A'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-x-2">
                            <button type="submit" name="assign_user_roles" class="bg-teal-custom text-black px-4 py-2 rounded">Save Roles</button>
                            <button type="submit" name="assign_custom_permissions" class="bg-teal-custom text-black px-4 py-2 rounded">Save Custom Permissions</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
</div>