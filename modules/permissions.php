<?php
require_once '../db.php';
require_once '../functions.php';

if (!has_permission('manage_permissions')) {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

$action = $_GET['action'] ?? 'list';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_role'])) {
        $role_name = $_POST['role_name'];
        $stmt = $db->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->bind_param('s', $role_name);
        $stmt->execute();
    } elseif (isset($_POST['add_permission'])) {
        $perm_name = $_POST['permission_name'];
        $stmt = $db->prepare("INSERT INTO permissions (name) VALUES (?)");
        $stmt->bind_param('s', $perm_name);
        $stmt->execute();
    } elseif (isset($_POST['assign_permissions'])) {
        $role_id = $_POST['role_id'];
        $permissions = $_POST['permissions'] ?? [];
        $db->query("DELETE FROM role_permissions WHERE role_id = $role_id");
        foreach ($permissions as $perm_id) {
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $role_id, $perm_id);
            $stmt->execute();
        }
    }
}

$roles = $db->query("SELECT * FROM roles")->fetch_all(MYSQLI_ASSOC);
$permissions = $db->query("SELECT * FROM permissions")->fetch_all(MYSQLI_ASSOC);
?>

<h1>Permissions Management</h1>
<nav>
    <a href="index.php?module=permissions&action=list">List Roles & Permissions</a> |
    <a href="index.php?module=permissions&action=add_role">Add Role</a> |
    <a href="index.php?module=permissions&action=add_permission">Add Permission</a>
</nav>

<?php if ($action === 'list'): ?>
    <h2>Roles and Permissions</h2>
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
                    $role_perms = $db->query("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = {$role['id']}")->fetch_all(MYSQLI_ASSOC);
                    echo implode(', ', array_column($role_perms, 'name'));
                    ?>
                </td>
                <td><a href="index.php?module=permissions&action=edit&role_id=<?php echo $role['id']; ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif ($action === 'add_role'): ?>
    <h2>Add New Role</h2>
    <form method="POST" class="permissions-form">
        <label>Role Name:</label>
        <input type="text" name="role_name" required>
        <button type="submit" name="add_role">Add Role</button>
    </form>
<?php elseif ($action === 'add_permission'): ?>
    <h2>Add New Permission</h2>
    <form method="POST" class="permissions-form">
        <label>Permission Name:</label>
        <input type="text" name="permission_name" required>
        <button type="submit" name="add_permission">Add Permission</button>
    </form>
<?php elseif ($action === 'edit' && isset($_GET['role_id'])): ?>
    <?php
    $role_id = $_GET['role_id'];
    $role = $db->query("SELECT * FROM roles WHERE id = $role_id")->fetch_assoc();
    $role_perms = array_column($db->query("SELECT permission_id FROM role_permissions WHERE role_id = $role_id")->fetch_all(MYSQLI_ASSOC), 'permission_id');
    ?>
    <h2>Edit Permissions for <?php echo htmlspecialchars($role['name']); ?></h2>
    <form method="POST" class="permissions-form">
        <input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
        <label>Permissions:</label>
        <?php foreach ($permissions as $perm): ?>
            <label>
                <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" <?php echo in_array($perm['id'], $role_perms) ? 'checked' : ''; ?>>
                <?php echo htmlspecialchars($perm['name']); ?>
            </label><br>
        <?php endforeach; ?>
        <button type="submit" name="assign_permissions">Save</button>
    </form>
<?php endif; ?>

