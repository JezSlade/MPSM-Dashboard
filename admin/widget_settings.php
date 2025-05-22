<?php
// admin/widget_settings.php
// v1.2.0 [Admin: Enable/Disable Widgets per User with CSRF]

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/permissions.php';
require_once __DIR__ . '/../core/tracking.php';

require_admin();
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function check_csrf(){ 
    if (empty($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) {
        http_response_code(400); die('Invalid CSRF token');
    }
}

$pdo    = get_db();
$defs   = $pdo->query("SELECT name,display_name FROM widgets ORDER BY name")->fetchAll();
$action = $_REQUEST['action'] ?? '';
$msg    = '';

// Handle update
if ($action==='update' && $_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $uid    = intval($_POST['user_id'] ?? 0);
    $chosen = $_POST['widgets'] ?? [];
    $upsert = $pdo->prepare("
      INSERT INTO user_widget_settings (user_id,widget,enabled)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE enabled = VALUES(enabled)
    ");
    foreach ($defs as $w) {
        $e = in_array($w['name'],$chosen) ? 1 : 0;
        $upsert->execute([$uid, $w['name'], $e]);
    }
    track_event('widget_settings_updated',['user_id'=>$uid,'widgets'=>$chosen]);
    $msg = 'Saved.';
}

$users = $pdo->query('SELECT id,username FROM users ORDER BY username')->fetchAll();
$stmt  = $pdo->prepare('SELECT enabled FROM user_widget_settings WHERE user_id = ? AND widget = ?');
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8"><title>Admin: Widget Settings</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
  <header class="flex justify-between items-center neu p-4 mb-4">
    <h1 class="text-xl">Admin: Widget Settings</h1>
    <nav>
      <a href="users.php" class="mr-4">Users</a>
      <a href="roles.php" class="mr-4">Roles</a>
      <a href="widget_settings.php" class="underline">Widgets</a>
      <a href="../index.php" class="ml-4 underline">Dashboard</a>
    </nav>
  </header>

  <?php if ($msg): ?>
    <div class="neu p-2 mb-4"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <?php foreach ($users as $u): ?>
  <section class="neu p-4 mb-6">
    <h2 class="widget-header mb-2">User: <?= htmlspecialchars($u['username']) ?></h2>
    <form method="POST" action="widget_settings.php?action=update" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
      <div class="grid grid-cols-3 gap-2">
        <?php foreach ($defs as $w): 
            $stmt->execute([$u['id'], $w['name']]);
            $row = $stmt->fetch();
            $enabled = $row ? (bool)$row['enabled'] : true;
        ?>
          <label>
            <input type="checkbox" name="widgets[]" value="<?= htmlspecialchars($w['name']) ?>" <?= $enabled?'checked':'' ?>>
            <?= htmlspecialchars($w['display_name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="neu p-2">Save</button>
    </form>
  </section>
  <?php endforeach; ?>
</body>
</html>
