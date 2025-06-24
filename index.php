<?php
// index.php
// -------------------------------------------------------------------
// Entrypoint for MPSM Dashboard SPA using Tailwind CSS + Feather Icons
// Glassmorphic/neon‐CMYK theme via utility classes
// -------------------------------------------------------------------
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2/dist/tailwind.min.css" rel="stylesheet">
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- Our JS helpers -->
  <script defer src="/js/api.js"></script>
  <script defer src="/js/ui_helpers.js"></script>
</head>
<body class="h-full flex flex-col overflow-hidden">

  <!-- 1) Global header -->
  <?php include __DIR__ . '/includes/header.php'; ?>

  <!-- 2) Customer nav -->
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <!-- 3) Main content area -->
  <main class="flex-1 overflow-y-auto p-4">
    <?php include __DIR__ . '/cards/CustomersCard.php'; ?>
    <!-- additional cards here -->
  </main>

  <!-- 4) Footer -->
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    // Activate Feather icons
    feather.replace({ 'stroke-width': 2, width: '1.25em', height: '1.25em' });
  </script>
</body>
</html>
