<?php
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';

if (!has_permission('manage_permissions')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}

// Define the mapping from permission names to module names
$module_permissions = [
    'view_dashboard' => 'Dashboard',
    'view_customers' => 'Customers',
    'view_devices' => 'Devices',
    'manage_permissions' => 'Permissions', // Permission related to the Permissions module itself
    'view_devtools' => 'DevTools',
    'view_status' => 'Status',
    // 'custom_access' is a general permission and will remain 'N/A' unless specifically mapped.
];


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
            header("Location: index.php?module=permissions&success=Role+added+successfully");
            exit;
        } elseif (isset($_POST['delete_role'])) {
            $role_id = $_POST['role_id'];
            $stmt = $db->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&success=Role+deleted+successfully");
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
            header("Location: index.php?module=permissions&success=Permission+added+successfully");
            exit;
        } elseif (isset($_POST['delete_permission'])) {
            $permission_id = $_POST['permission_id'];
            $stmt = $db->prepare("DELETE FROM permissions WHERE id = ?");
            $stmt->bind_param('i', $permission_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&success=Permission+deleted+successfully");
            exit;
        } elseif (isset($_POST['update_module_status'])) {
            $module_name = $_POST['module_name'];
            $active = isset($_POST['active']) ? 1 : 0;
            $stmt = $db->prepare("UPDATE modules SET active = ? WHERE name = ?");
            $stmt->bind_param('is', $active, $module_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&action=modules&success=Module+status+updated");
            exit;
        } elseif (isset($_POST['add_user'])) {
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role_id = $_POST['role_id'];

            if (empty($username) || empty($password)) {
                throw new Exception("Username and password are required.");
            }

            $stmt = $db->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $username, $password, $role_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&action=users&success=User+added+successfully");
            exit;
        } elseif (isset($_POST['delete_user'])) {
            $user_id = $_POST['user_id'];
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&action=users&success=User+deleted+successfully");
            exit;
        } elseif (isset($_POST['assign_role_permissions'])) {
            $role_id = $_POST['role_id'];
            $permission_ids = $_POST['permission_ids'] ?? [];

            $db->begin_transaction();
            $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($permission_ids)) {
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                foreach ($permission_ids as $perm_id) {
                    $stmt->bind_param('ii', $role_id, $perm_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            $db->commit();
            header("Location: index.php?module=permissions&action=roles&success=Role+permissions+updated");
            exit;
        } elseif (isset($_POST['assign_user_roles'])) {
            $user_id = $_POST['user_id'];
            $role_ids = $_POST['role_ids'] ?? [];

            $db->begin_transaction();
            $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($role_ids)) {
                $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                foreach ($role_ids as $role_id) {
                    $stmt->bind_param('ii', $user_id, $role_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            $db->commit();
            header("Location: index.php?module=permissions&action=users&success=User+roles+updated");
            exit;
        } elseif (isset($_POST['assign_custom_permissions'])) {
            $user_id = $_POST['user_id'];
            $custom_permission_ids = $_POST['custom_permissions'] ?? [];

            $db->begin_transaction();
            $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($custom_permission_ids)) {
                $sql = "INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                foreach ($custom_permission_ids as $perm_id) {
                    $stmt->bind_param('ii', $user_id, $perm_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            $db->commit();
            header("Location: index.php?module=permissions&action=users&success=User+custom+permissions+updated");
            exit;
        }
    } catch (Exception $e) {
        error_log("Permissions POST error: " . $e->getMessage());
        header("Location: index.php?module=permissions&error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Fetch data for display
$roles = $db->query("SELECT * FROM roles")->fetch_all(MYSQLI_ASSOC);
$permissions = $db->query("SELECT * FROM permissions")->fetch_all(MYSQLI_ASSOC);
$users = $db->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
$modules = $db->query("SELECT * FROM modules")->fetch_all(MYSQLI_ASSOC);

$all_permissions = $db->query("SELECT id, name FROM permissions")->fetch_all(MYSQLI_ASSOC);


// Get roles and permissions for display in forms
$available_roles = $db->query("SELECT id, name FROM roles")->fetch_all(MYSQLI_ASSOC);
$available_permissions = $db->query("SELECT id, name FROM permissions")->fetch_all(MYSQLI_ASSOC);

?>

<div class="glass p-6 rounded-lg shadow-neumorphic-dark">
    <h2 class="text-2xl text-cyan-neon mb-6">Permissions Management</h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="text-green-500 mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="text-red-500 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <div class="flex space-x-4 mb-6">
        <a href="?module=permissions&action=list" class="btn-primary <?php echo $action === 'list' ? 'active' : ''; ?>">Overview</a>
        <a href="?module=permissions&action=roles" class="btn-primary <?php echo $action === 'roles' ? 'active' : ''; ?>">Roles</a>
        <a href="?module=permissions&action=permissions" class="btn-primary <?php echo $action === 'permissions' ? 'active' : ''; ?>">Permissions</a>
        <a href="?module=permissions&action=users" class="btn-primary <?php echo $action === 'users' ? 'active' : ''; ?>">Users</a>
        <a href="?module=permissions&action=modules" class="btn-primary <?php echo $action === 'modules' ? 'active' : ''; ?>">Modules</a>
    </div>

    <?php if ($action === 'list'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">System Overview</h3>
        <p class="text-default">Manage roles, permissions, users, and module visibility within the system.</p>

    <?php elseif ($action === 'roles'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Roles</h3>
        <div class="mb-6">
            <h4 class="text-lg text-default mb-2">Add New Role</h4>
            <form method="POST" class="flex space-x-2 items-center">
                <input type="text" name="role_name" placeholder="Role Name" required class="flex-1 max-w-xs p-2 bg-black-smoke text-white rounded border border-gray-700">
                <button type="submit" name="add_role" class="btn-secondary">Add Role</button>
            </form>
        </div>

        <h4 class="text-lg text-default mb-2">Existing Roles</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="p-2">ID</th>
                        <th class="p-2">Name</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr class="border-b border-gray-700">
                            <td class="p-2"><?php echo htmlspecialchars($role['id']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($role['name']); ?></td>
                            <td class="p-2">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role and its associated permissions and user roles?');">
                                    <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                    <button type="submit" name="delete_role" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                </form>
                                <a href="?module=permissions&action=assign_role_permissions&role_id=<?php echo $role['id']; ?>" class="text-blue-500 hover:text-blue-700 text-sm ml-2">Assign Permissions</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'assign_role_permissions' && isset($_GET['role_id'])):
        $role_id = (int)$_GET['role_id'];
        $role_name_stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
        $role_name_stmt->bind_param('i', $role_id);
        $role_name_stmt->execute();
        $result_role_name = $role_name_stmt->get_result();
        $current_role = $result_role_name->fetch_assoc();
        $role_name_stmt->close();

        if ($current_role):
            $assigned_permissions = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
            $assigned_permissions->bind_param('i', $role_id);
            $assigned_permissions->execute();
            $result_assigned_perms = $assigned_permissions->get_result();
            $current_perm_ids = [];
            while($row = $result_assigned_perms->fetch_assoc()) {
                $current_perm_ids[] = $row['permission_id'];
            }
            $assigned_permissions->close();
        ?>
            <h3 class="text-xl text-yellow-neon mb-4">Assign Permissions for Role: <?php echo htmlspecialchars($current_role['name']); ?></h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
                <div>
                    <label for="permission_ids" class="block text-gray-300 mb-1">Select Permissions:</label>
                    <select id="permission_ids" name="permission_ids[]" multiple size="10" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                        <?php foreach ($available_permissions as $perm): ?>
                            <option value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $current_perm_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($perm['name']); ?> (Module: <?php echo $module_permissions[$perm['name']] ?? 'N/A'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="assign_role_permissions" class="btn-secondary">Save Permissions</button>
            </form>
        <?php else: ?>
            <p class="text-red-500">Role not found.</p>
        <?php endif; ?>

    <?php elseif ($action === 'permissions'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Permissions</h3>
        <div class="mb-6">
            <h4 class="text-lg text-default mb-2">Add New Permission</h4>
            <form method="POST" class="flex space-x-2 items-center">
                <input type="text" name="permission_name" placeholder="Permission Name" required class="flex-1 max-w-xs p-2 bg-black-smoke text-white rounded border border-gray-700">
                <button type="submit" name="add_permission" class="btn-secondary">Add Permission</button>
            </form>
        </div>

        <h4 class="text-lg text-default mb-2">Existing Permissions</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="p-2">ID</th>
                        <th class="p-2">Name</th>
                        <th class="p-2">Module</th> <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $perm): ?>
                        <tr class="border-b border-gray-700">
                            <td class="p-2"><?php echo htmlspecialchars($perm['id']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($perm['name']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($module_permissions[$perm['name']] ?? 'N/A'); ?></td> <td class="p-2">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                    <input type="hidden" name="permission_id" value="<?php echo $perm['id']; ?>">
                                    <button type="submit" name="delete_permission" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'users'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Users</h3>
        <div class="mb-6">
            <h4 class="text-lg text-default mb-2">Add New User</h4>
            <form method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-gray-300 mb-1">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Username" required class="w-full p-2 bg-black-smoke text-white rounded border border-gray-700">
                </div>
                <div>
                    <label for="password" class="block text-gray-300 mb-1">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Password" required class="w-full p-2 bg-black-smoke text-white rounded border border-gray-700">
                </div>
                <div>
                    <label for="role_id" class="block text-gray-300 mb-1">Default Role:</label>
                    <select id="role_id" name="role_id" required class="w-full p-2 bg-black-smoke text-white rounded border border-gray-700">
                        <?php foreach ($available_roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-secondary">Add User</button>
            </form>
        </div>

        <h4 class="text-lg text-default mb-2">Existing Users</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="p-2">ID</th>
                        <th class="p-2">Username</th>
                        <th class="p-2">Role</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user):
                        $user_role_name = 'N/A';
                        foreach ($roles as $role) {
                            if ($role['id'] == $user['role_id']) {
                                $user_role_name = $role['name'];
                                break;
                            }
                        }
                    ?>
                        <tr class="border-b border-gray-700">
                            <td class="p-2"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($user_role_name); ?></td>
                            <td class="p-2 flex space-x-2">
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                </form>
                                <a href="?module=permissions&action=assign_user_details&user_id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 text-sm">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'assign_user_details' && isset($_GET['user_id'])):
        $user_id = (int)$_GET['user_id'];
        $user_details_stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $user_details_stmt->bind_param('i', $user_id);
        $user_details_stmt->execute();
        $result_user_details = $user_details_stmt->get_result();
        $current_user = $result_user_details->fetch_assoc();
        $user_details_stmt->close();

        if ($current_user):
            // Fetch roles assigned to this user
            $user_roles_stmt = $db->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
            $user_roles_stmt->bind_param('i', $user_id);
            $user_roles_stmt->execute();
            $result_user_roles = $user_roles_stmt->get_result();
            $user_role_ids = [];
            while ($row = $result_user_roles->fetch_assoc()) {
                $user_role_ids[] = $row['role_id'];
            }
            $user_roles_stmt->close();

            // Fetch custom permissions assigned to this user
            $user_perms_stmt = $db->prepare("SELECT permission_id FROM user_permissions WHERE user_id = ?");
            $user_perms_stmt->bind_param('i', $user_id);
            $user_perms_stmt->execute();
            $result_user_perms = $user_perms_stmt->get_result();
            $user_perm_ids = [];
            while ($row = $result_user_perms->fetch_assoc()) {
                $user_perm_ids[] = $row['permission_id'];
            }
            $user_perms_stmt->close();
        ?>
            <h3 class="text-xl text-yellow-neon mb-4">Manage Details for User: <?php echo htmlspecialchars($current_user['username']); ?></h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div>
                    <label class="block text-gray-300 mb-1">Assign Roles:</label>
                    <select name="role_ids[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                        <?php foreach ($available_roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo in_array($role['id'], $user_role_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
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
    <?php elseif ($action === 'modules'): ?>
        <h3 class="text-xl text-yellow-neon mb-4">Manage Modules</h3>
        <p class="text-default mb-4">Toggle visibility for modules. Inactive modules will not appear in the navigation.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-left table-auto">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="p-2">Name</th>
                        <th class="p-2">Active</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>
                        <tr class="border-b border-gray-700">
                            <td class="p-2"><?php echo htmlspecialchars($module['name']); ?></td>
                            <td class="p-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    <?php echo $module['active'] ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>">
                                    <?php echo $module['active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="p-2">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="module_name" value="<?php echo $module['name']; ?>">
                                    <?php if ($module['name'] === 'dashboard' || $module['name'] === 'status'): ?>
                                        <button type="button" class="text-gray-500 text-sm cursor-not-allowed" disabled title="This module cannot be deactivated.">Cannot Deactivate</button>
                                    <?php else: ?>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="active" value="1" class="sr-only peer" onchange="this.form.submit()" <?php echo $module['active'] ? 'checked' : ''; ?>>
                                            <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            <span class="ms-3 text-sm font-medium text-gray-300"><?php echo $module['active'] ? 'Deactivate' : 'Activate'; ?></span>
                                        </label>
                                        <button type="submit" name="update_module_status" class="hidden">Update</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>