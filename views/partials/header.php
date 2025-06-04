<?php
/**
 * views/partials/header.php
 *
 * This header sits at the top of every page. It shows:
 *  - The application name (“MPSM Dashboard”)
 *  - The current version number (from config/permissions.php)
 *  - A dropdown to switch “role” (for testing permissions)
 *  - A logout link
 *
 * Assumes that SESSION holds a 'role' key (set to one of the roles from config/permissions.php).
 */

// If no role is set, default to the first one in the permissions list
if (! isset($_SESSION['role'])) {
    // Grab the keys from $permissions array to determine valid roles
    $allRoles = array_keys($permissions);
    $_SESSION['role'] = reset($allRoles);
}

// Handle role‐switch form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role'])) {
    $newRole = trim($_POST['switch_role']);
    if (array_key_exists($newRole, $permissions)) {
        $_SESSION['role'] = $newRole;
    }
    // Redirect to avoid form resubmission
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Grab the current version from a constant (defined in permissions.php)
$version = defined('APP_VERSION') ? APP_VERSION : 'v0.0.0';
$currentRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<!-- Note: this file is included inside <body> by index.php -->
<header class="top-bar">
  <div class="left">
    <h1>MPSM Dashboard</h1>
    <span class="version">Version <?= htmlspecialchars($version) ?></span>
  </div>
  <div class="right">
    <form method="POST" class="role-switcher">
      <label for="switch_role">Role:</label>
      <select name="switch_role" id="switch_role" onchange="this.form.submit()">
        <?php foreach ($permissions as $roleName => $_): ?>
          <option value="<?= htmlspecialchars($roleName) ?>"
            <?= $roleName === $currentRole ? 'selected' : '' ?>>
            <?= htmlspecialchars($roleName) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <noscript>
        <button type="submit">Switch</button>
      </noscript>
    </form>
    <a href="?action=logout" class="logout">Logout</a>
  </div>
</header>

<style>
  /* Minimal inline CSS to structure the header—feel free to move to styles.css */
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
  .role-switcher {
    margin-right: 1rem;
    font-size: 0.9rem;
  }
  .role-switcher label {
    margin-right: 0.25rem;
  }
  .role-switcher select {
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
