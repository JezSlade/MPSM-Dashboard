<?php
/**
 * cards/CardKPI.php — Dummy “KPI” card with multiple metrics
 */
?>
<div class="neumorphic p-4">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Key Performance Indicators</h2>
    <button class="neu-btn" aria-label="Settings">
      <i data-feather="settings"></i>
    </button>
  </header>
  <div class="grid grid-cols-3 gap-3 text-center">
    <div>
      <p class="text-sm opacity-75">Users</p>
      <p class="text-xl font-semibold">1,234</p>
      <p class="text-xs text-green-500">+5%</p>
    </div>
    <div>
      <p class="text-sm opacity-75">Orders</p>
      <p class="text-xl font-semibold">567</p>
      <p class="text-xs text-red-500">-2%</p>
    </div>
    <div>
      <p class="text-sm opacity-75">Revenue</p>
      <p class="text-xl font-semibold">$8.9K</p>
      <p class="text-xs text-green-500">+12%</p>
    </div>
  </div>

  <!--
  Changelog:
  - Created CardKPI.php to display three side-by-side KPI metrics.
  - Each metric shows value and trend indicator.
  -->
