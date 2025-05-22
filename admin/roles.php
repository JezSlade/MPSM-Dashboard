<?php
// admin/roles.php
// v1.2.0 [Admin CRUD for Roles with CSRF]

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/permissions.php';
require_once __DIR__ . '/../core/tracking.php';

require_admin();
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function check_csrf() {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        die('Invalid CSRF token');
    }
}

$pdo     = get_db();
$action  = $_REQUEST['action'] ?? '';
$message = '';
$allPerms = $pdo->query('SELECT id,name FROM permissions')->fetchAll();

// Create Role
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $r = trim($_POST['role_name'] ?? '');
    if ($r) {
        try {
            $pdo->prepare('INSERT INTO roles (name) VALUES (?)')->execute([$r]);
            track_event('role_created', ['role' => $r]);
            $message = 'Role created.';
        } catch (Exception $e) {
            $message = 'Error: '.$e->getMessage();
        }
    } else {
        $message = 'Role name required.';
    }
}

// Update Permissions
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $rid  = intval($_POST['role_id'] ?? 0);
    $sels = $_POST['permissions'] ?? [];
    $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$rid]);
    $ins  = $pdo->prepare('INSERT INTO role_permissions (role_id,permission_id) VALUES (?,?)');
    foreach ($sels as $pid) {
        $ins->execute([$rid, intval($pid)]);
    }
    track_event('role_permissions_updated', ['role_id'=>$rid,'permissions'=>$sels]);
    $message = 'Permissions updated.';
}

// Delete Role
if ($action === 'delete' && isset($_GET['id'])) {
    $rid = intval($_GET['id']);
    $name = $pdo->query("SELECT name FROM roles WHERE id = $rid")->fetchColumn();
    if ($name) {
        $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$rid]);
        $pdo->prepare('DELETE FROM user_roles WHERE role_id = ?')->execute([$rid]);
        $pdo->prepare('DELETE FROM roles WHERE id = ?')->execute([$rid]);
        track_event('role_deleted',['role_id'=>$rid,'role'=>$name]);
        $message = 'Role deleted.';
    }
}

// Fetch roles
$roles = $pdo->query('SELECT id,name FROM roles ORDER BY name')->fetchAll();
$stmtRP = $pdo->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <title>Admin: Roles</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
  <header class="flex justify-between items-center neu p-4 mb-4">
    <h1 class="text-xl">Admin: Roles</h1>
    <nav>
      <a href="users.php" class="mr-4">Users</a>
      <a href="roles.php" class="underline">Roles</a>
      <a href="widget_settings.php" class="mr-4">Widgets</a>
      <a href="../index.php" class="underline">Dashboard</a>
    </nav>
  </header>

  <?php if ($message): ?>
    <div class="neu p-2 mb-4"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <!-- Create Role -->
  <section class="neu p-4 mb-6">
    <h2 class="widget-header mb-2">Create Role</h2>
    <form method="POST" action="roles.php?action=create" class="space-y-2">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input name="role_name" placeholder="Role name" required class="border p-1 w-full">
      <button type="submit" class="neu p-2">Create</button>
    </form>
  </section>

  <!-- List & Manage Roles -->
  <section class="neu p-4">
    <h2 class="widget-header mb-2">Existing Roles</h2>
    <?php foreach ($roles as $r): 
        $stmtRP->execute([$r['id']]);
        $assigned = array_column($stmtRP->fetchAll(), 'permission_id');
    ?>
      <div class="neu p-4 mb-4">
        <h3 class="font-semibold"><?= htmlspecialchars($r['name']) ?></h3>
        <form method="POST" action="roles.php?action=update" class="space-y-2">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="role_id" value="<?= $r['id'] ?>">
          <div class="grid grid-cols-3 gap-2">
            <?php foreach ($allPerms as $p): ?>
              <label>
                <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                  <?= in_array($p['id'], $assigned) ? 'checked' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="neu p-1">Save Permissions</button>
          <a href="roles.php?action=delete&id=<?= $r['id'] ?>"
             onclick="return confirm('Delete role <?= htmlspecialchars($r['name']) ?>?')"
             class="underline text-red-600 ml-2">Delete</a>
        </form>
      </div>
    <?php endforeach; ?>
  </section>
</body>
</html>
