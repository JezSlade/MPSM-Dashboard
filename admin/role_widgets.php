<?php
// admin/role_widgets.php
// v1.2.0 [Admin: Assign Widgets to Roles with CSRF]

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/permissions.php';
require_once __DIR__ . '/../core/tracking.php';
require_once __DIR__ . '/../core/db.php';

require_permission('view_widgets');
// session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function check_csrf(){ 
    if (empty($_POST['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) {
        http_response_code(400); die('Invalid CSRF token');
    }
}

$pdo     = get_db();
$action  = $_REQUEST['action'] ?? '';
$message = '';
$roles   = $pdo->query("SELECT name FROM roles ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$widgets = $pdo->query("SELECT id,display_name,category FROM widgets ORDER BY display_name")->fetchAll();

// Handle update
if ($action==='update' && $_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $role = $_POST['role'] ?? '';
    $sel  = $_POST['widget_ids'] ?? [];
    $pdo->prepare("DELETE FROM role_widgets WHERE role_name = ?")->execute([$role]);
    $ins = $pdo->prepare("INSERT INTO role_widgets (role_name,widget_id) VALUES (?,?)");
    foreach ($sel as $wid) {
        $ins->execute([$role, intval($wid)]);
    }
    track_event('role_widgets_updated',['role'=>$role,'widgets'=>$sel]);
    $message = 'Saved.';
}

// Fetch current assignments
$stmt = $pdo->prepare("SELECT widget_id FROM role_widgets WHERE role_name = ?");
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8"><title>Admin: Role Widgets</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
  <header class="flex justify-between items-center neu p-4 mb-4">
    <h1 class="text-xl">Admin: Role Widgets</h1>
    <nav>
      <a href="widgets.php" class="mr-4">Widgets</a>
      <a href="role_widgets.php" class="underline">Role Widgets</a>
      <a href="../index.php" class="ml-4 underline">Dashboard</a>
    </nav>
  </header>

  <?php if ($message): ?>
    <div class="neu p-2 mb-4"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php foreach ($roles as $role): 
    $stmt->execute([$role]);
    $assigned = array_column($stmt->fetchAll(), 'widget_id');
  ?>
  <section class="neu p-4 mb-6">
    <h2 class="widget-header mb-2">Role: <?= htmlspecialchars($role) ?></h2>
    <form method="POST" action="role_widgets.php?action=update" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
      <div class="grid grid-cols-3 gap-2">
        <?php foreach ($widgets as $w): ?>
          <label>
            <input type="checkbox" name="widget_ids[]" value="<?= $w['id'] ?>"
              <?= in_array($w['id'],$assigned) ? 'checked' : '' ?>>
            <?= htmlspecialchars($w['display_name']) ?> (<?= htmlspecialchars($w['category']) ?>)
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="neu p-2">Save</button>
    </form>
  </section>
  <?php endforeach; ?>
</body>
</html>
