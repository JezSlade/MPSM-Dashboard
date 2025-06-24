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

  <!-- Tailwind via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- Global CSS -->
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <!-- Replace manual card includes with the dashboard view -->
  <?php include __DIR__ . '/views/dashboard.php'; ?>

  <?php include __DIR__ . '/components/SlideOutPanel.php'; ?>
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Activate Feather icons -->
  <script>feather.replace();</script>
  <!-- Card behaviors -->
  <script src="/public/js/card-interactions.js"></script>
</body>
</html>
