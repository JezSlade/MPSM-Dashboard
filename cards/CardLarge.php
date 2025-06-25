<?php
/**
 * cards/CardLarge.php — Dummy “Large” card (wider content)
 */
?>
<div class="neumorphic p-4">
<?php
$title = 'CardLarge';
$cardId = 'CardLarge';
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Large Data Overview</h2>
    <div class="flex space-x-1">
      <button class="neu-btn" aria-label="Minimize card">–</button>
      <button class="neu-btn" aria-label="Settings">
        <i data-feather="settings"></i>
      </button>
    </div>
  </header>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <p class="text-sm opacity-75">Metric A:</p>
      <p class="text-2xl font-semibold">123,456</p>
    </div>
    <div>
      <p class="text-sm opacity-75">Metric B:</p>
      <p class="text-2xl font-semibold">78.9%</p>
    </div>
    <div class="col-span-2">
      <p class="text-sm opacity-75">Description:</p>
      <p class="text-base">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.</p>
    </div>
  </div>

  <!--
  Changelog:
  - Created CardLarge.php to demonstrate a wide, two-column data card.
  - Contains header controls and a 2×2 grid of dummy metrics and text.
  -->

<!--
Changelog:
- Updated to use shared card_header.php include.
-->
