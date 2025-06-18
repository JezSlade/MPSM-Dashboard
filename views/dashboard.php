<?php
// /views/dashboard.php

// --- DEBUG BLOCK (Always at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

// 1) Scan card files
$cardsDir  = __DIR__ . '/../cards/';
$cardFiles = array_filter(
    scandir($cardsDir),
    fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'php'
);

// 2) Load visibility from cookie or default to all
$visibleCards = isset($_COOKIE['visible_cards'])
    ? explode(',', $_COOKIE['visible_cards'])
    : $cardFiles;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

  <!-- ⚙️ Gear icon for opening preferences -->
  <button class="gear-icon" title="View Preferences">⚙️</button>

  <!-- Preferences Modal Component -->
  <?php
    // Make $cardFiles and $visibleCards available to the component
    include __DIR__ . '/../components/preferences-modal.php';
  ?>

  <main class="glass-main">
    <div class="card-grid">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

</body>
</html>
