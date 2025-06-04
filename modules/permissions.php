<?php
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

if (!has_permission('manage_permissions')) {
    echo "<p class='error'>Access denied.</p>";
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
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $db->error);
            }
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
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $db->error);
            }
            $stmt->bind_param('s', $perm_name);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php?module=permissions&action=list");
            exit;
        } elseif (isset($_POST['assign_permissions'])) {
            $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
            if ($role_id === false || $role_id <= 0) {
                throw new Exception("Invalid role ID.");
            }
            $permissions = $_POST['permissions'] ?? [];
            // Sanitize permission IDs
            $permissions = array_filter($permissions, function($id) {
                return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
            });

            $db->query("DELETE FROM role_permissions WHERE role_id = $role_id"); // Note: Still needs sanitization
            foreach ($permissions as $perm_id) {
                $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $db->error);
                }
                $stmt->bind_param('ii', $role_id, $perm_id);
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

// Fetch roles and permissions with error handling
$result = $db->query("SELECT * FROM roles");
if ($result === false) {
    echo "<p class='error'>Error fetching roles: " . htmlspecialchars($db->error) . "</p>";
    exit;
}
$roles = $result->fetch_all(MYSQLI_ASSOC);

$result = $db->query("SELECT * FROM permissions");
if ($result === false) {
    echo "<p class='error'>Error fetching permissions: " . htmlspecialchars($db->error) . "</p>";
    exit;
}
$permissions = $result->fetch_all(MYSQLI_ASSOC);
?>

<h1>Permissions Management</h1>
<nav>
    <a href="index.php?module=permissions&action=list">List Roles & Permissions</a> |
    <a href="index.php?module=permissions&action=add_role">Add Role</a> |
    <a href="index.php?module=permissions&action=add_permission">Add Permission</a>
</nav>

<?php if ($action === 'list'): ?>
    <h2>Roles and Permissions</h2>
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
                        if ($result === false) {
                            echo "Error: " . htmlspecialchars($db->error);
                        } else {
                            $role_perms = $result->fetch_all(MYSQLI_ASSOC);
                            if (empty($role_perms)) {
                                echo "None";
                            } else {
                                $perm_names = array_column($role_perms, 'name');
                                echo htmlspecialchars(implode(', ', $perm_names));
                            }
                        }
                        ?>
                    </td>
                    <td><a href="index.php?module=permissions&action=edit&role_id=<?php echo $role['id']; ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
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
    $role_id = filter_input(INPUT_GET, 'role_id', FILTER_VALIDATE_INT);
    if ($role_id === false || $role_id <= 0) {
        echo "<p class='error'>Invalid role ID.</p>";
    } else {
        $result = $db->query("SELECT * FROM roles WHERE id = $role_id");
        if ($result === false) {
            echo "<p class='error'>Error fetching role: " . htmlspecialchars($db->error) . "</p>";
        } else {
            $role = $result->fetch_assoc();
            if (!$role) {
                echo "<p class='error'>Role not found.</p>";
            } else {
                $result = $db->query("SELECT permission_id FROM role_permissions WHERE role_id = $role_id");
                if ($result === false) {
                    echo "<p class='error'>Error fetching role permissions: " . htmlspecialchars($db->error) . "</p>";
                } else {
                    $role_perms = array_column($result->fetch_all(MYSQLI_ASSOC), 'permission_id');
                    ?>
                    <h2>Edit Permissions for <?php echo htmlspecialchars($role['name']); ?></h2>
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
                            <button type="submit" name="assign_permissions">Save</button>
                        <?php endif; ?>
                    </form>
                    <?php
                }
            }
        }
    }
?>
<?php endif; ?>