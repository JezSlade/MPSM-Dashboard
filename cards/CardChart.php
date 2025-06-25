<?php
/**
 * cards/CardChart.php — Dummy “Chart” card
 */
?>
<div class="neumorphic p-4">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Sales Trend</h2>
    <div class="flex space-x-1">
      <button class="neu-btn" aria-label="Minimize card">–</button>
      <button class="neu-btn" aria-label="Settings">
        <i data-feather="settings"></i>
      </button>
    </div>
  </header>
  <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
    <span class="opacity-50">[Chart Placeholder]</span>
  </div>

  <!--
  Changelog:
  - Created CardChart.php to demonstrate a card with chart space.
  - Placeholder box simulates where a chart (canvas/SVG) would go.
  -->
