<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/includes/config.php';

// Load card visibility preferences
$cardsDir = __DIR__ . '/cards/';
$cardFiles = array_filter(scandir($cardsDir), fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php');

// Default: show all
$visibleCards = isset($_COOKIE['visible_cards']) ? explode(',', $_COOKIE['visible_cards']) : $cardFiles;

// Exclude view-preferences card from grid (it’s always loaded separately)
$visibleCards = array_filter($visibleCards, fn($f) => $f !== 'view-preferences-card.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= APP_NAME ?> — Dashboard</title>
  <link rel="stylesheet" href="assets/styles.css">
  <style>
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
      gap: 1.5rem;
      padding: 1.5rem;
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/cards/view-preferences-card.php'; ?>

  <main class="glass-main">
    <div class="card-grid">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

</body>
</html>
