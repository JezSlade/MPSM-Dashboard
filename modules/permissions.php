<?php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!has_permission('manage_permissions')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

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
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
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

<h1>Permissions Management 🎛️</h1>
<nav>
    <a href="index.php?module=permissions&action=list">📋 List</a> |
    <a href="index.php?module=permissions&action=add_role">➕ Role</a> |
    <a href="index.php?module=permissions&action=add_permission">➕ Perm</a> |
    <a href="index.php?module=permissions&action=add_user">➕ User</a>
</nav>

<?php if ($action === 'list'): ?>
    <h2>Roles and Permissions 🌐</h2>
    <?php if (empty($roles)): ?>
        <p>No roles found. Add a role to get started.</p>
    <?php else: ?>
        <table class="permissions-table">
            <tr>
                <th>Role</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?php echo htmlspecialchars($role['name']); ?></td>
                    <td>
                        <?php
                        $role_id = (int)$role['id'];
                        $result = $db->query("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = $role_id");
                        $role_perms = $result->fetch_all(MYSQLI_ASSOC);
                        echo htmlspecialchars(implode(', ', array_column($role_perms, 'name')) ?: 'None');
                        ?>
                    </td>
                    <td><a href="index.php?module=permissions&action=edit&role_id=<?php echo $role['id']; ?>">✏️ Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <h2>Users and Permissions 👤</h2>
    <?php if (empty($users)): ?>
        <p>No users found. Add a user to get started.</p>
    <?php else: ?>
        <table class="permissions-table">
            <tr>
                <th>Username</th>
                <th>Roles</th>
                <th>Custom Permissions</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars(implode(', ', $user_roles[$user['id']] ?? ['None'])); ?></td>
                    <td><?php echo htmlspecialchars(implode(', ', $user_custom_perms[$user['id']] ?? ['None'])); ?></td>
                    <td><a href="index.php?module=permissions&action=edit_user&user_id=<?php echo $user['id']; ?>">✏️ Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php elseif ($action === 'add_role'): ?>
    <h2>Add New Role ➕</h2>
    <form method="POST" class="permissions-form">
        <label>Role Name:</label>
        <input type="text" name="role_name" required>
        <button type="submit" name="add_role">🎯 Add Role</button>
    </form>
<?php elseif ($action === 'add_permission'): ?>
    <h2>Add New Permission ➕</h2>
    <form method="POST" class="permissions-form">
        <label>Permission Name:</label>
        <input type="text" name="permission_name" required>
        <button type="submit" name="add_permission">🎯 Add Perm</button>
    </form>
<?php elseif ($action === 'add_user'): ?>
    <h2>Add New User ➕</h2>
    <form method="POST" class="permissions-form">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label>Roles:</label>
        <select name="roles[]" multiple size="5">
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_user">🎯 Add User</button>
    </form>
<?php elseif ($action === 'edit' && isset($_GET['role_id'])): ?>
    <?php
    $role_id = filter_input(INPUT_GET, 'role_id', FILTER_VALIDATE_INT);
    if ($role_id === false || $role_id <= 0): ?>
        <p class='error'>Invalid role ID.</p>
    <?php else: ?>
        <?php
        $result = $db->query("SELECT * FROM roles WHERE id = $role_id");
        if ($result === false): ?>
            <p class='error'>Error fetching role: <?php echo htmlspecialchars($db->error); ?></p>
        <?php else: ?>
            <?php
            $role = $result->fetch_assoc();
            if (!$role): ?>
                <p class='error'>Role not found.</p>
            <?php else: ?>
                <?php
                $result = $db->query("SELECT permission_id FROM role_permissions WHERE role_id = $role_id");
                $role_perms = array_column($result->fetch_all(MYSQLI_ASSOC), 'permission_id');
                ?>
                <h2>Edit Permissions for <?php echo htmlspecialchars($role['name']); ?> ✏️</h2>
                <form method="POST" class="permissions-form">
                    <input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
                    <label>Permissions:</label>
                    <?php if (empty($permissions)): ?>
                        <p>No permissions available. Add a permission first.</p>
                    <?php else: ?>
                        <?php foreach ($permissions as $perm): ?>
                            <label>
                                <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $role_perms) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($perm['name']); ?>
                            </label><br>
                        <?php endforeach; ?>
                        <button type="submit" name="assign_permissions">🎯 Save</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php elseif ($action === 'edit_user' && isset($_GET['user_id'])): ?>
    <?php
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
    if ($user_id === false || $user_id <= 0): ?>
        <p class='error'>Invalid user ID.</p>
    <?php else: ?>
        <?php
        $result = $db->query("SELECT * FROM users WHERE id = $user_id");
        if ($result === false): ?>
            <p class='error'>Error fetching user: <?php echo htmlspecialchars($db->error); ?></p>
        <?php else: ?>
            <?php
            $user = $result->fetch_assoc();
            if (!$user): ?>
                <p class='error'>User not found.</p>
            <?php else: ?>
                <?php
                $result = $db->query("SELECT role_id FROM user_roles WHERE user_id = $user_id");
                $user_role_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'role_id');
                $result = $db->query("SELECT permission_id FROM user_permissions WHERE user_id = $user_id");
                $user_perm_ids = array_column($result->fetch_all(MYSQLI_ASSOC), 'permission_id');
                ?>
                <h2>Edit <?php echo htmlspecialchars($user['username']); ?> ✏️</h2>
                <form method="POST" class="permissions-form">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <label>Roles:</label>
                    <select name="roles[]" multiple size="5">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo in_array($role['id'], $user_role_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Custom Permissions:</label>
                    <select name="custom_permissions[]" multiple size="5">
                        <?php foreach ($permissions as $perm): ?>
                            <option value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $user_perm_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($perm['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_user_roles">🎯 Save Roles</button>
                    <button type="submit" name="assign_custom_permissions">🎯 Save Custom Perms</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>