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
  <!-- Global CSS (glassmorphic, slide-out panel, etc.) -->
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/navigation.php'; ?>

  <main class="flex-1 overflow-y-auto p-4 space-y-6">
    <?php include __DIR__ . '/cards/CustomersCard.php'; ?>
  </main>

  <!-- Reusable Slide-Out Panel for any table‐row drilldown -->
  <?php include __DIR__ . '/components/SlideOutPanel.php'; ?>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Activate Feather icons -->
  <script>feather.replace();</script>

  <!-- Button behaviors -->
  <script>
    (function(){
      const htmlEl = document.documentElement;

      // Theme toggle
      document.getElementById('theme-toggle').addEventListener('click', () => {
        htmlEl.classList.toggle('dark');
      });

      // Clear session cookies
      document.getElementById('clear-session').addEventListener('click', () => {
        document.cookie.split(';').forEach(c => {
          const name = c.split('=')[0].trim();
          document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
        });
        location.reload();
      });

      // Hard refresh (bypass cache)
      document.getElementById('refresh-all').addEventListener('click', () => {
        location.reload();
      });
    })();
  </script>

  <!-- Global card & table interactions (sorting, expand, drilldown, slide-out) -->
  <script src="/public/js/card-interactions.js"></script>
</body>
</html>
