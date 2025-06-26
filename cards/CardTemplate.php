<?php
/**
 * CardTemplate.php — Generic helper card template
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file

// Required header variables (these should be REMOVED if this is used as a template, as index.php provides them)
// $title = 'CardTemplate';       // Display name in the card header
// $cardId = 'CardTemplate';      // Must match the filename (without .php)
// $allowMinimize = true;         // Show minimize button
// $allowSettings = true;         // Show settings button
// $allowClose = false;           // Close button (not enabled by default)
include __DIR__ . '/../includes/card_header.php';
?>

<div class="neumorphic p-4">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Template Section</h2>
    <button class="neu-btn" aria-label="Settings"><i data-feather="settings"></i></button>
  </header>

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

  <div class="mt-4 max-h-40 overflow-y-auto bg-gray-100 dark:bg-gray-800 p-2 rounded text-sm">
    <p>This is a scrollable area for content like logs or lists. You can fill it with dynamic data.</p>
    <p>More content...</p>
    <p>Even more content...</p>
    <p>Scroll down to see the end.</p>
  </div>
</div>