<?php
/**
 * views/partials/sidebar.php
 * Dynamically generates sidebar links from $modules.
 */
if (! isset($modules) || ! is_array($modules)) {
    return;
}
?>
<nav class="sidebar">
  <ul>
    <?php foreach ($modules as $modName => $modPath): ?>
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
