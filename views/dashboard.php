<?php
// views/dashboard.php
// -------------------------------------------------------------------
// Dashboard view: brings in navigation once, then cards.
// -------------------------------------------------------------------
?>
<main class="main-display">
  <!-- 1) Navigation dropdown -->
  <?php include __DIR__ . '/../includes/navigation.php'; ?>

  <!-- 2) All cards render in here -->
  <?php include __DIR__ . '/../cards/CustomersCard.php'; ?>
</main>
