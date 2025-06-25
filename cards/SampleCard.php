<?php
/**
 * SampleCard.php — Proof-of-concept card.
 * Demonstrates the card template and Neumorphic styling.
 */
?>
<div class="neumorphic p-4">
<?php
$title = 'SampleCard';
$cardId = 'SampleCard';
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium">Sample Card</h2>
    <div class="flex space-x-1">
      <button class="neu-btn" aria-label="Minimize card">–</button>
      <button class="neu-btn" aria-label="Card settings">
        <i data-feather="settings"></i>
      </button>
    </div>
  </header>
  <div class="card-body">
    <p class="text-sm">This is a placeholder content area for the Sample Card.</p>
  </div>
</div>

<!--
Changelog:
- Updated to use shared card_header.php include.
-->
