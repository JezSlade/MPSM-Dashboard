<?php
// views/dashboard.php
// -------------------------------------------------------------------
// Single SPA view: only one navigation include, then cards.
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
    
    <!-- 1. Only one navigation dropdown -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <!-- 2. Main scrollable content: cards only -->
    <main class="main-display">
      <?php include __DIR__ . '/../cards/CustomersCard.php'; ?>
      <!-- future cards here -->
    </main>
    
    <!-- 3. Fixed footer -->
    <footer class="app-footer">
      &copy; <?= date('Y') ?> Resolutions by Design
    </footer>
    
  </div>

</body>
</html>
