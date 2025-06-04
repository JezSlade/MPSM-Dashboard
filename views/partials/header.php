<?php
/**
 * views/partials/header.php
 *
 * Renders the top bar with:
 *  - App Name (“MPSM Dashboard”)
 *  - Version (APP_VERSION)
 *  - A “User” dropdown to impersonate existing users (from users table)
 *  - A “Logout” link that clears the session’s user_id
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include permissions so we have APP_VERSION and current_user()
require_once __DIR__ . '/../../config/permissions.php';
$pdo = require __DIR__ . '/../../config/db.php';

$current = current_user(); // array or null

// Handle user-switch form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_user'])) {
    $newUserId = (int) $_POST['switch_user'];
    // Verify the user ID exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$newUserId]);
    if ($stmt->fetchColumn()) {
        $_SESSION['user_id'] = $newUserId;
    } else {
        // If invalid, revert to guest
        $_SESSION['user_id'] = null;
    }
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user_id']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Fetch all users to populate the dropdown
$stmt = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
$allUsers = $stmt->fetchAll();

// Determine display name
$displayName = $current ? $current['username'] : 'guest';
?>
<!DOCTYPE html>
<!-- This is included by index.php; no <html> or <head> tags here -->
<header class="top-bar">
  <div class="left">
    <h1>MPSM Dashboard</h1>
    <span class="version">Version <?= htmlspecialchars(APP_VERSION) ?></span>
  </div>
  <div class="right">
    <form method="POST" class="user-switcher">
      <label for="switch_user">User:</label>
      <select name="switch_user" id="switch_user" onchange="this.form.submit()">
        <?php foreach ($allUsers as $u): ?>
          <option value="<?= (int)$u['id'] ?>"
            <?= ($current && $current['id'] === (int)$u['id']) || (!$current && $u['username']==='guest')
                ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <noscript>
        <button type="submit">Switch</button>
      </noscript>
    </form>
    <?php if ($current): ?>
      <a href="?action=logout" class="logout">Logout</a>
    <?php endif; ?>
  </div>
</header>

<style>
  body, html { margin: 0; padding: 0; }
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
  .logout {
    color: #f44;
    text-decoration: none;
    font-size: 0.9rem;
    margin-left: 1rem;
  }
  .logout:hover {
    color: #f88;
  }
</style>
