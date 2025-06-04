<?php
/**
 * views/partials/sidebar.php
 *
 * Dynamically renders the sidebar links by querying available modules from DB,
 * and only shows those modules the current user has permission to view.
 */

$pdo = require __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/permissions.php';

$current = current_user(); // may be null if guest

// Fetch all modules
$stmt = $pdo->query("SELECT name FROM modules ORDER BY name ASC");
$allModules = $stmt->fetchAll(PDO::FETCH_COLUMN);

?>
<nav class="sidebar">
  <ul>
    <?php foreach ($allModules as $modName): ?>
      <?php if (user_has_permission($modName)): ?>
        <li<?php if ($modName === $module) echo ' class="active"'; ?>>
          <a href="?module=<?= urlencode($modName) ?>">
            <?= htmlspecialchars($modName) ?>
          </a>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</nav>

<style>
  .sidebar {
    width: 200px;
    background: #222;
    color: #ddd;
    height: 100vh;
    position: fixed;
    top: 3rem; /* adjust if header is 3rem high */
    left: 0;
    overflow-y: auto;
    font-family: Consolas, monospace;
  }
  .sidebar ul { list-style: none; padding: 0; margin: 0; }
  .sidebar li {
    padding: 0.75rem 1rem;
  }
  .sidebar li.active,
  .sidebar li:hover {
    background: #333;
  }
  .sidebar li a {
    color: inherit;
    text-decoration: none;
    display: block;
  }
  .sidebar li a:hover {
    color: #0ff;
  }
  .main-wrapper {
    margin-left: 200px; /* leave room for the sidebar */
    margin-top: 3rem;   /* leave room for the header */
    padding: 1rem;
  }
</style>
