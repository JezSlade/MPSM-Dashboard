<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get version
$version = '0.0.0.0';
$verFile = __DIR__ . '/../../version.txt';
if (file_exists($verFile)) {
    $version = trim(file_get_contents($verFile));
}
// Handle user switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_user'])) {
    $newUserId = (int)$_POST['switch_user'];
    $pdo = require __DIR__ . '/../../config/db.php';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id=?');
    $stmt->execute([$newUserId]);
    if ($stmt->fetchColumn()) {
        $_SESSION['user_id'] = $newUserId;
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
?>
<header class="top-bar">
  <div class="left">
    <h1>MPSM Dashboard</h1>
    <span class="version">Version <?= htmlspecialchars($version) ?></span>
  </div>
  <div class="right">
    <form method="POST" class="user-switcher">
      <label for="switch_user">User:</label>
      <select name="switch_user" id="switch_user" onchange="this.form.submit()">
        <?php
        $pdo = require __DIR__ . '/../../config/db.php';
        $stmtAll = $pdo->query('SELECT id,username FROM users ORDER BY username ASC');
        $allUsers = $stmtAll->fetchAll();
        $current = current_user();
        $currentId = $current ? $current['id'] : null;
        foreach ($allUsers as $u) {
            $sel = ($u['id'] === $currentId) ? 'selected' : '';
            echo "<option value="{$u['id']}" $sel>" . htmlspecialchars($u['username']) . "</option>";
        }
        ?>
      </select>
      <noscript><button type="submit">Switch</button></noscript>
    </form>
  </div>
</header>
