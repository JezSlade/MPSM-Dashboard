<?php
/**
 * cards/CardList.php — Dummy “List” card (activity feed)
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file
?>
<div class="neumorphic p-4">
<?php
// $title = 'CardList'; // REMOVED (managed by index.php)
// $cardId = 'CardList'; // REMOVED (managed by index.php)
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Recent Activity</h2>
    <button class="neu-btn" aria-label="Settings">
      <i data-feather="settings"></i>
    </button>
  </header>
  <ul class="space-y-2 max-h-48 overflow-y-auto">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <li class="p-2 bg-gray-100 dark:bg-gray-800 rounded flex justify-between">
        <span>Activity item #<?php echo $i; ?></span>
        <span class="text-xs opacity-50">Just now</span>
      </li>
    <?php endfor; ?>
  </ul>