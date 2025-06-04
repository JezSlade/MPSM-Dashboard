<?php
/**
 * views/partials/sidebar.php
 *
 * Renders the left‐hand navigation inside MPSM. Only display modules
 * the logged‐in user is allowed to see (per config/permissions.php).
 */
?>
<nav class="sidebar">
  <ul>
    <?php if (user_has_permission('Dashboard')): ?>
      <li><a href="?module=Dashboard">Dashboard</a></li>
    <?php endif; ?>

    <?php if (user_has_permission('Customers')): ?>
      <li><a href="?module=Customers">Customers</a></li>
    <?php endif; ?>

    <?php if (user_has_permission('DevTools')): ?>
      <!-- Changed “developer” → “DevTools” so it matches the module key in index.php -->
      <li><a href="?module=DevTools">Dev Tools</a></li>
    <?php endif; ?>
  </ul>
</nav>
