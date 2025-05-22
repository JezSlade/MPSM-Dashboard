<?php
// admin/users.php
// v1.2.0 [Admin CRUD for Users with CSRF & ARGON2ID]

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/permissions.php';
require_once __DIR__ . '/../core/tracking.php';

require_admin();  // only Admin & Developer

session_start();
// Generate a CSRF token if we don’t have one
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo     = get_db();
$action  = $_REQUEST['action'] ?? '';
$message = '';

// CSRF check function
function check_csrf(): void {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        die('Invalid CSRF token');
    }
}

// -----------------------------
// Create User
// -----------------------------
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u && $p) {
        $h = password_hash($p, PASSWORD_ARGON2ID);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, NOW())');
        try {
            $stmt->execute([$u, $h]);
            $uid = $pdo->lastInsertId();
            track_event('user_created', ['user_id' => $uid, 'username' => $u]);
            $message = 'User created.';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    } else {
        $message = 'Username & password required.';
    }
}

// -----------------------------
// Edit User
// -----------------------------
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $id = intval($_POST['id'] ?? 0);
    $u  = trim($_POST['username'] ?? '');
    $p  = $_POST['password'] ?? '';

    if ($id && $u) {
        if ($p) {
            $h = password_hash($p, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare('UPDATE users SET username = ?, password_hash = ? WHERE id = ?');
            $stmt->execute([$u, $h, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username = ? WHERE id = ?');
            $stmt->execute([$u, $id]);
        }
        track_event('user_edited', ['user_id' => $id, 'username' => $u]);
        $message = 'User updated.';
    } else {
        $message = 'Username required.';
    }
}

// -----------------------------
// Delete User
// -----------------------------
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Confirm via GET only (no CSRF here)—optionally could require POST
    $name = $pdo->query("SELECT username FROM users WHERE id = $id")->fetchColumn();
    if ($name) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        track_event('user_deleted', ['user_id' => $id, 'username' => $name]);
        $message = 'User deleted.';
    }
}

// Fetch all users
$users = $pdo->query('SELECT id, username, created_at FROM users ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <title>Admin: Users</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">

  <header class="flex justify-between items-center neu p-4 mb-4">
    <h1 class="text-xl">Admin: Users</h1>
    <nav>
      <a href="users.php" class="underline">Users</a>
      <a href="roles.php" class="ml-4">Roles</a>
      <a href="widget_settings.php" class="ml-4">Widgets</a>
      <a href="../index.php" class="ml-4 underline">Dashboard</a>
    </nav>
  </header>

  <?php if ($message): ?>
    <div class="neu p-2 mb-4"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <!-- Create User Form -->
  <section class="neu p-4 mb-6">
    <h2 class="widget-header mb-2">Create User</h2>
    <form method="POST" action="users.php?action=create" class="space-y-2">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input name="username" placeholder="Username" required class="border p-1 w-full">
      <input type="password" name="password" placeholder="Password" required class="border p-1 w-full">
      <button type="submit" class="neu p-2">Create</button>
    </form>
  </section>

  <!-- List & Edit Users -->
  <section class="neu p-4">
    <h2 class="widget-header mb-2">Existing Users</h2>
    <table class="w-full table-auto mb-4">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= $u['created_at'] ?></td>
          <td>
            <a href="users.php?action=edit&id=<?= $u['id'] ?>" class="underline mr-2">Edit</a>
            <a href="users.php?action=delete&id=<?= $u['id'] ?>"
               onclick="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')"
               class="underline text-red-600">
              Delete
            </a>
          </td>
        </tr>
        <?php if (isset($_GET['action'], $_GET['id'])
                 && $_GET['action'] === 'edit'
                 && intval($_GET['id']) === $u['id']): ?>
        <tr>
          <td colspan="4">
            <form method="POST" action="users.php?action=edit" class="flex space-x-2">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <input name="username" value="<?= htmlspecialchars($u['username']) ?>" required class="border p-1">
              <input type="password" name="password" placeholder="New password" class="border p-1">
              <button type="submit" class="neu p-1">Save</button>
            </form>
          </td>
        </tr>
        <?php endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</body>
</html>
