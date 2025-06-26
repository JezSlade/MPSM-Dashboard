<?php
/**
 * cards/CardSmall.php — Dummy “Small” card (compact summary)
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file
?>
<div class="neumorphic p-3">
<?php
// $title = 'CardSmall'; // REMOVED (managed by index.php)
// $cardId = 'CardSmall'; // REMOVED (managed by index.php)
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-1">
    <h2 class="font-medium text-base">Quick Stat</h2>
    <button class="neu-btn" aria-label="Settings">
      <i data-feather="settings"></i>
    </button>
  </header>
  <p class="text-xl font-semibold text-center">42</p>