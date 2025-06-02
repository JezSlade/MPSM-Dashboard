<?php
// admin/widgets.php
// v1.2.0 [Admin CRUD for Widgets with CSRF]

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

// Create
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $fields = [
      $_POST['name'], $_POST['display_name'], $_POST['description'] ?? '',
      $_POST['category'] ?? '', $_POST['endpoint'] ?? '',
      $_POST['params'] ? json_encode(json_decode($_POST['params'],true)) : null,
      $_POST['method'], $_POST['permission'], $_POST['help_link'] ?? ''
    ];
    $stmt = $pdo->prepare("
      INSERT INTO widgets
        (name,display_name,description,category,endpoint,params,method,permission,help_link)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    try {
      $stmt->execute($fields);
      track_event('widget_created',['name'=>$_POST['name']]);
      $message = 'Widget created.';
    } catch (Exception $e) {
      $message = 'Error: '.$e->getMessage();
    }
}

// Edit
if ($action==='edit' && $_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $id = intval($_POST['id']);
    $fields = [
      $_POST['display_name'], $_POST['description'] ?? '',
      $_POST['category'] ?? '', $_POST['endpoint'] ?? '',
      $_POST['params'] ? json_encode(json_decode($_POST['params'],true)) : null,
      $_POST['method'], $_POST['permission'], $_POST['help_link'] ?? '', $id
    ];
    $stmt = $pdo->prepare("
      UPDATE widgets SET
        display_name=?,description=?,category=?,endpoint=?,params=?,
        method=?,permission=?,help_link=?
      WHERE id=?
    ");
    try {
      $stmt->execute($fields);
      track_event('widget_edited',['id'=>$id]);
      $message = 'Widget updated.';
    } catch (Exception $e) {
      $message = 'Error: '.$e->getMessage();
    }
}

// Delete
if ($action==='delete' && isset($_GET['id'])) {
    $id   = intval($_GET['id']);
    $name = $pdo->query("SELECT name FROM widgets WHERE id=$id")->fetchColumn();
    if ($name) {
        $pdo->prepare("DELETE FROM widgets WHERE id=?")->execute([$id]);
        track_event('widget_deleted',['id'=>$id,'name'=>$name]);
        $message = 'Widget deleted.';
    }
}

$widgets = $pdo->query("
  SELECT id,name,display_name,category,method,permission
  FROM widgets ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head><meta charset="UTF-8"><title>Admin: Widgets</title>
<link rel="stylesheet" href="../assets/css/style.css"></head>
<body class="p-4">
  <header class="flex justify-between items-center neu p-4 mb-4">
    <h1 class="text-xl">Admin: Widgets</h1>
    <nav>
      <a href="widgets.php" class="underline">Widgets</a>
      <a href="role_widgets.php" class="ml-4">Role Widgets</a>
      <a href="../index.php" class="ml-4 underline">Dashboard</a>
    </nav>
  </header>

  <?php if($message): ?><div class="neu p-2 mb-4"><?=htmlspecialchars($message)?></div><?php endif; ?>

  <!-- Create -->
  <section class="neu p-4 mb-6">
    <h2 class="widget-header mb-2">Create Widget</h2>
    <form method="POST" action="widgets.php?action=create" class="grid gap-2">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input name="name" placeholder="Internal Name" required class="border p-1">
      <input name="display_name" placeholder="Display Title" required class="border p-1">
      <textarea name="description" placeholder="Description" class="border p-1"></textarea>
      <input name="category" placeholder="Category" class="border p-1">
      <input name="endpoint" placeholder="Endpoint URL" class="border p-1">
      <textarea name="params" placeholder='Params JSON' class="border p-1"></textarea>
      <input name="method" placeholder="count|list|table|custom|dashboard" class="border p-1">
      <input name="permission" placeholder="Permission" class="border p-1">
      <input name="help_link" placeholder="Help Link URL" class="border p-1">
      <button type="submit" class="neu p-2">Create</button>
    </form>
  </section>

  <!-- List & Edit -->
  <section class="neu p-4">
    <h2 class="widget-header mb-2">Existing Widgets</h2>
    <table class="w-full table-auto mb-4">
      <thead>
        <tr><th>Name</th><th>Title</th><th>Category</th><th>Method</th><th>Perm</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach($widgets as $w): ?>
        <tr>
          <td><?=htmlspecialchars($w['name'])?></td>
          <td><?=htmlspecialchars($w['display_name'])?></td>
          <td><?=htmlspecialchars($w['category'])?></td>
          <td><?=htmlspecialchars($w['method'])?></td>
          <td><?=htmlspecialchars($w['permission'])?></td>
          <td>
            <a href="widgets.php?action=edit&id=<?=$w['id']?>" class="underline mr-2">Edit</a>
            <a href="widgets.php?action=delete&id=<?=$w['id']?>" onclick="return confirm('Delete?')" class="underline text-red-600">Delete</a>
          </td>
        </tr>
        <?php if($_GET['action']==='edit' && intval($_GET['id'])===intval($w['id'])): ?>
        <tr><td colspan="6">
          <form method="POST" action="widgets.php?action=edit" class="grid gap-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?=$w['id']?>">
            <input name="display_name" value="<?=htmlspecialchars($w['display_name'])?>" required class="border p-1">
            <textarea name="description" class="border p-1"><?=htmlspecialchars($w['description'])?></textarea>
            <input name="category" value="<?=htmlspecialchars($w['category'])?>" class="border p-1">
            <input name="endpoint" value="<?=htmlspecialchars($w['endpoint'])?>" class="border p-1">
            <textarea name="params" class="border p-1"><?=htmlspecialchars($w['params'])?></textarea>
            <input name="method" value="<?=htmlspecialchars($w['method'])?>" class="border p-1">
            <input name="permission" value="<?=htmlspecialchars($w['permission'])?>" class="border p-1">
            <input name="help_link" value="<?=htmlspecialchars($w['help_link'])?>" class="border p-1">
            <button type="submit" class="neu p-2">Save</button>
          </form>
        </td></tr>
        <?php endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</body>
</html>
