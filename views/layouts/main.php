<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=<?= file_exists(__DIR__ . '/../../version.txt') ? trim(file_get_contents(__DIR__ . '/../../version.txt')) : '0.0.0.0' ?>">
  <script src="assets/js/main.js?v=<?= file_exists(__DIR__ . '/../../version.txt') ? trim(file_get_contents(__DIR__ . '/../../version.txt')) : '0.0.0.0' ?>" defer></script>
</head>
<body>
  <?php include __DIR__ . '/../partials/header.php'; ?>
  <div class="wrapper">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="main-content">
      <?= $content ?>
    </div>
  </div>
  <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
