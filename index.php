<?php
// index.php — Entrypoint for the SPA
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// No global dropdown here—cards handle selection themselves via PHP
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
  <!-- Your global CSS (glassmorphic, slide-out panel, etc.) -->
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <main class="flex-1 overflow-y-auto p-4 space-y-6">
    <?php include __DIR__ . '/cards/CustomersCard.php'; ?>
    <?php include __DIR__ . '/cards/DeviceCountersCard.php'; ?>
    <?php include __DIR__ . '/cards/SupplyAlertsCard.php'; ?>
  </main>

  <?php include __DIR__ . '/components/SlideOutPanel.php'; ?>
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Activate Feather icons -->
  <script>feather.replace();</script>

  <!-- Global card behaviors: sort, expand, drilldown, slide‐out -->
  <script src="/public/js/card-interactions.js"></script>
</body>
</html>
