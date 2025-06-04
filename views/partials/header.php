```php
<?php
/**
 * views/partials/header.php
 *
 * Renders the top bar with:
 *  - App Name (“MPSM Dashboard”)
 *  - Dynamic Version read from version.txt
 *  - A “User” dropdown to switch between users
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Read version.txt (fallback to “0.0.0.0” if missing)
$version = '0.0.0.0';
$verFile = __DIR__ . '/../../version.txt';
if (file_exists($verFile)) {
    $version = trim(file_get_contents($verFile));
}

// Handle user‐switch form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_user'])) {
    $newUserId = (int) $_POST['switch_user'];
    // Verify the user ID exists
    $pdo = require __DIR__ . '/../../config/db.php';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$newUserId]);
    if ($stmt->fetchColumn()) {
        $_SESSION['user_id'] = $newUserId;
    }
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
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
        // Populate dropdown with all users
        $pdo = require __DIR__ . '/../../config/db.php';
        $stmtAll = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
        $allUsers = $stmtAll->fetchAll();
        $current = current_user();
        $currentId = $current ? $current['id'] : null;
        foreach ($allUsers as $u) {
            $sel = ($u['id'] === $currentId) ? 'selected' : '';
            echo "<option value=\"{$u['id']}\" $sel>" . htmlspecialchars($u['username']) . "</option>";
        }
        ?>
      </select>
      <noscript><button type="submit">Switch</button></noscript>
    </form>
  </div>
</header>

<style>
  body, html {
    margin: 0;
    padding: 0;
  }
  .top-bar {
    background: #111;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
    font-family: Consolas, monospace;
  }
  .top-bar .left h1 {
    margin: 0;
    font-size: 1.25rem;
    display: inline-block;
  }
  .top-bar .left .version {
    margin-left: 1rem;
    font-size: 0.9rem;
    color: #888;
  }
  .top-bar .right {
    display: flex;
    align-items: center;
  }
  .user-switcher {
    margin-right: 1rem;
    font-size: 0.9rem;
  }
  .user-switcher label {
    margin-right: 0.25rem;
  }
  .user-switcher select {
    font-family: Consolas, monospace;
  }
</style>
```
