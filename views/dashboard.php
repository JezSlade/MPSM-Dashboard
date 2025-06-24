<?php
// views/dashboard.php
// -------------------------------------------------------------------
// Full SPA HTML head + body wrapper + card include.
// -------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <!-- CORRECT stylesheet reference -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <script defer src="/js/api.js"></script>
  <script defer src="/js/ui_helpers.js"></script>
</head>
<body>

  <div id="app" class="app-container">

    <!-- Global header (logo + theme toggle) -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Navigation dropdown -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <!-- Cards area -->
    <main class="main-display">
      <?php include __DIR__ . '/../cards/CustomersCard.php'; ?>
      <!-- additional cards… -->
    </main>

    <!-- Global footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

  </div>
</body>
</html>
