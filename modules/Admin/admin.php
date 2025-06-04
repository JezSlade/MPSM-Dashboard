<?php
/**
 * modules/Admin/admin.php
 *
 * Full Admin panel:
 *  1) Manage Roles (CRUD)
 *  2) Manage Users (CRUD & assign to roles)
 *  3) Manage Modules (CRUD)
 *  4) Manage Role→Module Permissions (via checkboxes)
 *
 * We assume:
 *  - config/permissions.php is already loaded (via index.php), as is session.
 *  - user_has_permission('Admin') will be true (otherwise 403). 
 *  - A valid PDO instance is available via config/db.php
 */

// Protect against direct access
if (!defined('ROOT_DIR')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// If somehow the user has no “Admin” permission, don’t show this.
if (! user_has_permission('Admin')) {
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to access Admin.');
}

$pdo = require __DIR__ . '/../../config/db.php';

// Helper to redirect back to avoid form re‐submission
function redirect_here() {
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 1) Handle Form Submissions (Add/Delete Roles, Users, Modules, Permissions)

// 1A) Create a new role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_role'])) {
    $roleName = trim($_POST['create_role']);
    if ($roleName !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
            $stmt->execute([$roleName]);
        } catch (Exception $e) {
            // ignore duplicate or error
        }
    }
    redirect_here();
}

// 1B) Delete a role (by ID)
if (isset($_GET['delete_role_id'])) {
    $rId = (int) $_GET['delete_role_id'];
    // Do not allow deleting “Guest” (role_id=1)
    $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->execute([$rId]);
    $rName = $stmt->fetchColumn();
    if ($rName !== 'Guest') {
        $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$rId]);
    }
    redirect_here();
}

// 1C) Create a new module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_module'])) {
    $modName = trim($_POST['create_module']);
    if ($modName !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO modules (name) VALUES (?)");
            $stmt->execute([$modName]);
        } catch (Exception $e) {
            // ignore duplicate or error
        }
    }
    redirect_here();
}

// 1D) Delete a module (by ID)
if (isset($_GET['delete_module_id'])) {
    $mId = (int) $_GET['delete_module_id'];
    $stmt = $pdo->prepare("SELECT name FROM modules WHERE id = ?");
    $stmt->execute([$mId]);
    $mName = $stmt->fetchColumn();
    if (! in_array($mName, ['Dashboard','Admin'], true)) {
        // Prevent deleting Dashboard or Admin itself
        $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$mId]);
    }
    redirect_here();
}

// 1E) Create a new user (username + plaintext password + role_id)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['create_user']);
    $password = $_POST['password'] ?? '';
    $roleId   = (int) ($_POST['role_id'] ?? 0);
    if ($username !== '' && $password !== '' && $roleId > 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("
              INSERT INTO users (username, password_hash, role_id)
              VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $hash, $roleId]);
        } catch (Exception $e) {
            // ignore errors (e.g. duplicate username)
        }
    }
    redirect_here();
}

// 1F) Delete a user (by ID)
if (isset($_GET['delete_user_id'])) {
    $uId = (int) $_GET['delete_user_id'];
    // Prevent deleting the “guest” user (username=guest)
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$uId]);
    $uName = $stmt->fetchColumn();
    if ($uName !== 'guest') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uId]);
    }
    redirect_here();
}

// 1G) Update Role→Module permissions (checkboxes)
// This expects form fields like “perm_R3_M5” meaning “role_id=3, module_id=5, checkbox=on/off”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    // First, remove all existing role_module links
    $pdo->exec("DELETE FROM role_module");

    // Now, re‐insert any checked boxes
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'perm_') === 0 && $val === 'on') {
            // Format: perm_R{roleId}_M{moduleId}
            if (preg_match('/^perm_R(\d+)_M(\d+)$/', $key, $matches)) {
                $rId = (int)$matches[1];
                $mId = (int)$matches[2];
                // Insert link (ignore duplicates)
                $stmt = $pdo->prepare("
                  INSERT IGNORE INTO role_module (role_id, module_id)
                  VALUES (?, ?)
                ");
                $stmt->execute([$rId, $mId]);
            }
        }
    }
    redirect_here();
}

// ────────────────────────────────────────────────────────────────────────────
// 2) Fetch data for display in the Admin UI

// 2A) Roles
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll();

// 2B) Modules
$modules = $pdo->query("SELECT id, name FROM modules ORDER BY name ASC")->fetchAll();

// 2C) Users
$users = $pdo->query("
  SELECT u.id, u.username, r.name AS role_name, r.id AS role_id
  FROM users u
  JOIN roles r ON u.role_id = r.id
  ORDER BY u.username ASC
")->fetchAll();

// 2D) Role‐Module permission matrix (fetch all existing role_module)
$roleModuleLinks = [];
$stmt = $pdo->query("SELECT role_id, module_id FROM role_module");
foreach ($stmt->fetchAll() as $row) {
    $roleModuleLinks[$row['role_id']][$row['module_id']] = true;
}

// ────────────────────────────────────────────────────────────────────────────
// 3) Render the Admin page (HTML + inline CSS + forms)

?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8">
  <title>Admin Module – MPSM Dashboard</title>
  <style>
    body { margin: 0; font-family: Consolas, monospace; }
    .top-bar { background: #111; color: #fff; padding: 0.5rem 1rem; display: flex; justify-content: space-between; align-items: center; }
    .top-bar h1 { margin: 0; font-size: 1.25rem; }
    .main-wrapper { display: flex; }
    .sidebar { width: 200px; background: #222; color: #ddd; height: 100vh; position: fixed; top: 2.5rem; left: 0; overflow-y: auto; }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar li { padding: 0.75rem 1rem; }
    .sidebar li.active, .sidebar li:hover { background: #333; }
    .sidebar a { color: inherit; text-decoration: none; display: block; }
    .sidebar a:hover { color: #0ff; }
    .content { margin-left: 200px; margin-top: 2.5rem; padding: 1rem; }
    h2 { margin-top: 2rem; border-bottom: 1px solid #444; padding-bottom: 0.5rem; color: #eee; }
    table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
    th, td { border: 1px solid #555; padding: 0.5rem; text-align: left; }
    th { background: #333; color: #fff; }
    tr:nth-child(even) { background: #2a2a2a; }
    input[type="text"], input[type="password"], select { font-family: Consolas, monospace; padding: 0.25rem; }
    button, input[type="submit"] { padding: 0.3rem 0.7rem; margin-top: 0.5rem; font-size: 0.9rem; }
    form.inline { display: inline; }
    .section { margin-top: 2rem; }
  </style>
</head><body>

  <!-- Top Bar -->
  <header class="top-bar">
    <h1>Admin Module</h1>
    <span>Logged in as: <?= htmlspecialchars(current_user()['username'] ?? 'guest') ?></span>
  </header>

  <!-- Sidebar (re‐use the same code as views/partials/sidebar.php) -->
  <div class="sidebar">
    <ul>
      <?php foreach ($modules as $mod): ?>
        <?php $modName = $mod['name']; ?>
        <?php if (user_has_permission($modName)): ?>
          <?php $isActive = ($module === $modName) ? ' class="active"' : ''; ?>
          <li<?= $isActive ?>><a href="?module=<?= urlencode($modName) ?>"><?= htmlspecialchars($modName) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Main Content Area -->
  <div class="content">

    <h2>1) Roles</h2>
    <!-- Add New Role -->
    <form method="POST">
      <input type="text" name="create_role" placeholder="New role name" required>
      <button type="submit">Add Role</button>
    </form>

    <!-- List Existing Roles -->
    <table>
      <thead>
        <tr><th>ID</th><th>Name</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($roles as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td>
              <?php if ($r['name'] !== 'Guest'): ?>
                <a href="?module=Admin&delete_role_id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete role <?= htmlspecialchars($r['name']) ?>?')">Delete</a>
              <?php else: ?>
                &mdash; <!-- Don’t allow deleting Guest -->
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>2) Users</h2>
    <!-- Add New User -->
    <form method="POST">
      <input type="text" name="create_user" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role_id" required>
        <option value="">Assign Role…</option>
        <?php foreach ($roles as $r): ?>
          <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Add User</button>
    </form>

    <!-- List Existing Users -->
    <table>
      <thead>
        <tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['role_name']) ?></td>
            <td>
              <?php if ($u['username'] !== 'guest'): ?>
                <a href="?module=Admin&delete_user_id=<?= (int)$u['id'] ?>" onclick="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')">Delete</a>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>3) Modules</h2>
    <!-- Add New Module -->
    <form method="POST">
      <input type="text" name="create_module" placeholder="Module name" required>
      <button type="submit">Add Module</button>
    </form>

    <!-- List Existing Modules -->
    <table>
      <thead>
        <tr><th>ID</th><th>Name</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $m): ?>
          <tr>
            <td><?= (int)$m['id'] ?></td>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td>
              <?php if (!in_array($m['name'], ['Dashboard','Admin'])): ?>
                <a href="?module=Admin&delete_module_id=<?= (int)$m['id'] ?>" onclick="return confirm('Delete module <?= htmlspecialchars($m['name']) ?>?')">Delete</a>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>4) Role → Module Permissions</h2>
    <form method="POST">
      <input type="hidden" name="save_permissions" value="1">
      <table>
        <thead>
          <tr>
            <th>Role \ Module</th>
            <?php foreach ($modules as $m): ?>
              <th><?= htmlspecialchars($m['name']) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($roles as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <?php foreach ($modules as $m): ?>
                <td style="text-align:center;">
                  <input type="checkbox"
                    name="perm_R<?= (int)$r['id'] ?>_M<?= (int)$m['id'] ?>"
                    <?= isset($roleModuleLinks[$r['id']][$m['id']]) ? 'checked' : '' ?>>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button type="submit">Save Permissions</button>
    </form>

  </div>
</body></html>
