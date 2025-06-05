<?php
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

if (!has_permission('manage_permissions')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}

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
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['add_permission'])) {
            $perm_name = trim($_POST['permission_name']);
            if (empty($perm_name)) {
                throw new Exception("Permission name is required.");
            }
            $stmt = $db->prepare("INSERT INTO permissions (name) VALUES (?)");
            $stmt->bind_param('s', $perm_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['add_user'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            if (empty($username) || empty($password)) {
                throw new Exception("Username and password are required.");
            }
            $stmt = $db->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, 1)");
            $stmt->bind_param('ss', $username, $password);
            $stmt->execute();
            $user_id = $db->insert_id;
            if (isset($_POST['roles']) && is_array($_POST['roles'])) {
                foreach ($_POST['roles'] as $role_id) {
                    $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $stmt->bind_param('ii', $user_id, $role_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['assign_permissions'])) {
            $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
            if ($role_id === false || $role_id <= 0) {
                throw new Exception("Invalid role ID.");
            }
            $permissions = $_POST['permissions'] ?? [];
            $permissions = array_filter($permissions, function($id) {
                return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
            });
            $db->query("DELETE FROM role_permissions WHERE role_id = $role_id");
            foreach ($permissions as $perm_id) {
                $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $role_id, $perm_id);
                $stmt->execute();
                $stmt->close();
            }
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['assign_user_roles'])) {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                throw new Exception("Invalid user ID.");
            }
            $roles = $_POST['roles'] ?? [];
            $roles = array_filter($roles, function($id) {
                return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
            });
            $db->query("DELETE FROM user_roles WHERE user_id = $user_id");
            foreach ($roles as $role_id) {
                $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $user_id, $role_id);
                $stmt->execute();
                $stmt->close();
            }
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['assign_custom_permissions'])) {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                throw new Exception("Invalid user ID.");
            }
            $permissions = $_POST['custom_permissions'] ?? [];
            $permissions = array_filter($permissions, function($id) {
                return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
            });
            $db->query("DELETE FROM user_permissions WHERE user_id = $user_id");
            foreach ($permissions as $perm_id) {
                $stmt = $db->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $user_id, $perm_id);
                $stmt->execute();
                $stmt->close();
            }
            header("Location: index.php?module=permissions&action=list");
            exit;
        }
    } catch (Exception $e) {
        echo "<p class='text-red-500 p-4'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Fetch data
$result = $db->query("SELECT * FROM roles");
$roles = $result->fetch_all(MYSQLI_ASSOC);

$result = $db->query("SELECT * FROM permissions");
$permissions = $result->fetch_all(MYSQLI_ASSOC);

$result = $db->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);

$result = $db->query("SELECT ur.user_id, r.name AS role_name FROM user_roles ur JOIN roles r ON ur.role_id = r.id");
$user_roles = [];
while ($row = $result->fetch_assoc()) {
    $user_roles[$row['user_id']][] = $row['role_name'];
}

$result = $db->query("SELECT up.user_id, p.name AS perm_name FROM user_permissions up JOIN permissions p ON up.permission_id = p.id");
$user_custom_perms = [];
while ($row = $result->fetch_assoc()) {
    $user_custom_perms[$row['user_id']][] = $row['perm_name'];
}
?>

<div class="space-y-6">
    <h1 class="text-2xl text-cyan-neon">Permissions Management</h1>
    <nav class="flex space-x-4 text-gray-300">
        <a href="index.php?module=permissions&action=list" class="flex items-center hover:text-yellow-neon">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            List
        </a>
        <a href="index.php?module=permissions&action=add_role" class="flex items-center hover:text-yellow-neon">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Role
        </a>
        <a href="index.php?module=permissions&action=add_permission" class="flex items-center hover:text-yellow-neon">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Permission
        </a>
        <a href="index.php?module=permissions&action=add_user" class="flex items-center hover:text-yellow-neon">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add User
        </a>
    </nav>

    <?php if ($action === 'list'): ?>
        <h2 class="text-xl text-cyan-neon">Roles and Permissions</h2>
        <?php if (empty($roles)): ?>
            <p class="text-gray-300">No roles found. Add a role to get started.</p>
        <?php else: ?>
            <div class="glass p-4">
                <table class="w-full text-gray-300">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="py-2 px-4 text-left">Role</th>
                            <th class="py-2 px-4 text-left">Permissions</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr class="border-b border-gray-800">
                                <td class="py-2 px-4"><?php echo htmlspecialchars($role['name']); ?></td>
                                <td class="py-2 px-4">
                                    <?php
                                    $role_id = (int)$role['id'];
                                    $result = $db->query("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = $role_id");
                                    $role_perms = $result->fetch_all(MYSQLI_ASSOC);
                                    echo htmlspecialchars(implode(', ', array_column($role_perms, 'name')) ?: 'None');
                                    ?>
                                </td>
                                <td class="py-2 px-4">
                                    <a href="index.php?module=permissions&action=edit&role_id=<?php echo $role['id']; ?>" class="text-yellow-neon flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="text-xl text-cyan-neon mt-6">Users and Permissions</h2>
        <?php if (empty($users)): ?>
            <p class="text-gray-300">No users found. Add a user to get started.</p>
        <?php else: ?>
            <div class="glass p-4">
                <table class="w-full text-gray-300">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="py-2 px-4 text-left">Username</th>
                            <th class="py-2 px-4 text-left">Roles</th>
                            <th class="py-2 px-4 text-left">Custom Permissions</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-800">
                                <td class="py-2 px-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars(implode(', ', $user_roles[$user['id']] ?? ['None'])); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars(implode(', ', $user_custom_perms[$user['id']] ?? ['None'])); ?></td>
                                <td class="py-2 px-4">
                                    <a href="index.php?module=permissions&action=edit_user&user_id=<?php echo $user['id']; ?>" class="text-yellow-neon flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php elseif ($action === 'add_role'): ?>
        <h2 class="text-xl text-cyan-neon">Add New Role</h2>
        <form method="POST" class="glass p-4 space-y-4">
            <div>
                <label class="block text-gray-300 mb-1">Role Name:</label>
                <input type="text" name="role_name" required class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
            </div>
            <button type="submit" name="add_role" class="bg-teal-custom text-black px-4 py-2 rounded">Add Role</button>
        </form>
    <?php elseif ($action === 'add_permission'): ?>
        <h2 class="text-xl text-cyan-neon">Add New Permission</h2>
        <form method="POST" class="glass p-4 space-y-4">
            <div>
                <label class="block text-gray-300 mb-1">Permission Name:</label>
                <input type="text" name="permission_name" required class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
            </div>
            <button type="submit" name="add_permission" class="bg-teal-custom text-black px-4 py-2 rounded">Add Permission</button>
        </form>
    <?php elseif ($action === 'add_user'): ?>
        <h2 class="text-xl text-cyan-neon">Add New User</h2>
        <form method="POST" class="glass p-4 space-y-4">
            <div>
                <label class="block text-gray-300 mb-1">Username:</label>
                <input type="text" name="username" required class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Password:</label>
                <input type="password" name="password" required class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Roles:</label>
                <select name="roles[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_user" class="bg-teal-custom text-black px-4 py-2 rounded">Add User</button>
        </form>
    <?php elseif ($action === 'edit' && isset($_GET['role_id'])): ?>
        <?php
        $role_id = filter_input(INPUT_GET, 'role_id', FILTER_VALIDATE_INT);
        if ($role_id === false || $role_id <= 0): ?>
            <p class='text-red-500 p-4'>Invalid role ID.</p>
        <?php else: ?>
            <?php
            $result = $db->query("SELECT * FROM roles WHERE id = $role_id");
            if ($result === false): ?>
                <p class='text-red-500 p-4'>Error fetching role: <?php echo htmlspecialchars($db->error); ?></p>
            <?php else: ?>
                <?php
                $role = $result->fetch_assoc();
                if (!$role): ?>
                    <p class='text-red-500 p-4'>Role not found.</p>
                <?php else: ?>
                    <?php
                    $result = $db->query("SELECT permission_id FROM role_permissions WHERE role_id = $role_id");
                    $role_perms = array_column($result->fetch_all(MYSQLI_ASSOC), 'permission_id');
                    ?>
                    <h2 class="text-xl text-cyan-neon">Edit Permissions for <?php echo htmlspecialchars($role['name']); ?></h2>
                    <form method="POST" class="glass p-4 space-y-4">
                        <input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
                        <div>
                            <label class="block text-gray-300 mb-1">Permissions:</label>
                            <?php if (empty($permissions)): ?>
                                <p class="text-gray-300">No permissions available. Add a permission first.</p>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($permissions as $perm): ?>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $role_perms) ? 'checked' : ''; ?> class="mr-2">
                                            <span><?php echo htmlspecialchars($perm['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" name="assign_permissions" class="bg-teal-custom text-black px-4 py-2 rounded">Save</button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ($action === 'edit_user' && isset($_GET['user_id'])): ?>
        <?php
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
        if ($user_id === false || $user_id <= 0): ?>
            <p class='text-red-500 p-4'>Invalid user ID.</p>
        <?php else: ?>
            <?php
            $result = $db->query("SELECT * FROM users WHERE id = $user_id");
            if ($result === false): ?>
                <p class='text-red-500 p-4'>Error fetching user: <?php echo htmlspecialchars($db->error); ?></p>
            <?php else: ?>
                <?php
                $user = $result->fetch_assoc();
                if (!$user): ?>
                    <p class='text-red-500 p-4'>User not found.</p>
                <?php else: ?>
                    <?php
                    $result = $db->query("SELECT role_id FROM user_roles WHERE user_id = $user_id");
                    $user_role_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'role_id');
                    $result = $db->query("SELECT permission_id FROM user_permissions WHERE user_id = $user_id");
                    $user_perm_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'permission_id');
                    ?>
                    <h2 class="text-xl text-cyan-neon">Edit <?php echo htmlspecialchars($user['username']); ?></h2>
                    <form method="POST" class="glass p-4 space-y-4">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <div>
                            <label class="block text-gray-300 mb-1">Roles:</label>
                            <select name="roles[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>" <?php echo in_array($role['id'], $user_role_ids) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-1">Custom Permissions:</label>
                            <select name="custom_permissions[]" multiple size="5" class="w-full bg-black-smoke text-white p-2 rounded border border-gray-700">
                                <?php foreach ($permissions as $perm): ?>
                                    <option value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $user_perm_ids) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($perm['name']); ?>
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
        <?php endif; ?>
    <?php endif; ?>
</div>