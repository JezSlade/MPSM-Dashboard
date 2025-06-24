<?php
// index.php — Entrypoint for the SPA
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <!-- Global CSS -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col">

  <!-- Header (logo + neon icons) -->
  <?php include __DIR__ . '/includes/header.php'; ?>

  <!-- Navigation dropdown -->
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <!-- Main scrollable content -->
  <main class="flex-1 overflow-y-auto p-4">
    <?php include __DIR__ . '/cards/CustomersCard.php'; ?>
  </main>

  <!-- Footer -->
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    if (window.feather) feather.replace();
  </script>
</body>
</html>
