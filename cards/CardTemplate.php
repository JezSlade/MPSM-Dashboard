<?php
/**
 * CardTemplate.php — Generic helper card template
 * This template is intended to be duplicated and customized.
 * It uses neumorphic styling, dynamic header inclusion, and simulated content.
 */

// Required header variables
$title = 'CardTemplate';       // Display name in the card header
$cardId = 'CardTemplate';      // Must match the filename (without .php)
$allowMinimize = true;         // Show minimize button
$allowSettings = true;         // Show settings button
$allowClose = false;           // Close button (not enabled by default)
include __DIR__ . '/../includes/card_header.php';
?>

<!-- Card content starts here -->
<div class="neumorphic p-4">
  <!-- Optional section heading -->
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Template Section</h2>
    <button class="neu-btn" aria-label="Settings"><i data-feather="settings"></i></button>
  </header>

  <!-- Example of a 3-column metric layout -->
  <div class="grid grid-cols-3 gap-3 text-center">
    <div>
      <p class="text-sm text-gray-500">Metric A</p>
      <p class="text-xl font-semibold">123</p>
      <p class="text-xs text-green-500">+5%</p>
    </div>
    <div>
      <p class="text-sm text-gray-500">Metric B</p>
      <p class="text-xl font-semibold">456</p>
      <p class="text-xs text-red-500">-2%</p>
    </div>
    <div>
      <p class="text-sm text-gray-500">Metric C</p>
      <p class="text-xl font-semibold">789</p>
      <p class="text-xs text-green-500">+8%</p>
    </div>
  </div>

  <!-- Placeholder for scrollable content (e.g., list or log) -->
  <div class="mt-4 max-h-40 overflow-y-auto bg-gray-100 dark:bg-gray-800 p-2 rounded text-sm">
    <p>Item 1: Lorem ipsum</p>
    <p>Item 2: Dolor sit amet</p>
    <p>Item 3: Consectetur adipiscing</p>
    <p>Item 4: Elit sed do eiusmod</p>
  </div>
</div>

<!-- Optional script section (e.g., for icon refresh) -->
<script>
  if (window.feather) feather.replace();
</script>

<!--
Changelog:
- Template built from merged structure of CardKPI, CardList, and SampleCard
- Includes: title, 3-metric layout, scrollable section, Feather icons
-->
