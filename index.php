<?php
// File: index.php
// -----------------
// The main dashboard homepage.
// We set $pageTitle, then include header.php, then output page content, then include footer.php.

// 1. OPTIONAL: set a custom page title for the <head> section.
$pageTitle = 'MPSM Dashboard ‐ Home';

require_once __DIR__ . '/header.php';
?>

  <!-- ========== Begin index.php-specific content ========== -->
  <section class="dashboard-overview">
    <h1 class="section-title">Welcome to MPSM Dashboard</h1>

    <p>
      Use the navigation above to view customers, check device statuses,
      drill down on alerts, and more.
    </p>

    <!-- Example of a “card” container on the homepage -->
    <div class="card" style="margin-top: 1.5rem;">
      <h2 class="sub-title">Quick Actions</h2>
      <div style="display: flex; gap: 1rem; margin-top: 0.75rem;">
        <a href="/customer_list.php" class="btn-neon">View Customers</a>
        <a href="/device_list.php" class="btn-outline">View Devices</a>
      </div>
    </div>
  </section>
  <!-- ========== End index.php-specific content ========== -->

<?php
require_once __DIR__ . '/footer.php';
