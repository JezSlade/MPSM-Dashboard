<?php
$pageTitle = 'MPSM Dashboard ‐ Home';
require_once __DIR__ . '/header.php';
?>
  <section class="dashboard-overview">
    <h1 class="section-title">Welcome to MPSM Dashboard</h1>
    <p>Use the navigation above to view customers, check device statuses, drill down on alerts, and more.</p>
    <div class="card" style="margin-top: 1.5rem;">
      <h2 class="sub-title">Quick Actions</h2>
      <div style="display: flex; gap: 1rem; margin-top: 0.75rem;">
        <a href="/customer_list.php" class="btn-neon">View Customers</a>
        <a href="/device_list.php" class="btn-outline">View Devices</a>
      </div>
    </div>
  </section>
<?php
require_once __DIR__ . '/footer.php';
?>