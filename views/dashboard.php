<?php
// views/dashboard.php
// -------------------------------------------------------------------
// SPA layout ensuring only a single customer dropdown (navigation).
// -------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <script defer src="/js/api.js"></script>
  <script defer src="/js/ui_helpers.js"></script>
</head>
<body>

  <div id="app" class="app-container">
    
    <!-- Header / Navigation: only one customer select -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <!-- Main scrolling area: cards only, no dropdowns here -->
    <main class="main-display">
      <!-- Customers card: contains only table and refresh -->
      <?php include __DIR__ . '/../cards/CustomersCard.php'; ?>
      <!-- future cards go here -->
    </main>
    
    <!-- Footer fixed at bottom -->
    <footer class="app-footer">
      &copy; <?= date('Y') ?> Resolutions by Design — All rights reserved.
    </footer>
    
  </div>

</body>
</html>
