<nav class="sidebar">
  <ul>
    <?php if (user_has_permission('Dashboard')): ?>
      <li><a href="?module=Dashboard">Dashboard</a></li>
    <?php endif; ?>

    <?php if (user_has_permission('Customers')): ?>
      <li><a href="?module=Customers">Customers</a></li>
    <?php endif; ?>

    <?php if (user_has_permission('DevTools')): ?>
      <li><a href="?module=DevTools">Dev Tools</a></li>
    <?php endif; ?>
  </ul>
</nav>
