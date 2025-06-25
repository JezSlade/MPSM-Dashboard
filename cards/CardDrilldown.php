<?php
/**
 * cards/CardDrilldown.php — Dummy card demonstrating a drill-down modal overlay
 *
 * When you click the “View Details” button, a full-screen Neumorphic overlay appears,
 * showing more in-depth information, and can be closed by clicking the close icon.
 */
?>
<div class="neumorphic p-4">
<?php
$title = 'CardDrilldown';
$cardId = 'CardDrilldown';
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Drill-Down Card</h2>
    <button id="drillOpen" class="neu-btn" aria-label="Drill down into card">
      <i data-feather="eye"></i>
    </button>
  </header>
  <p class="text-sm opacity-75">
    Click the eye icon to drill down into detailed view.
  </p>

  <!-- Drill-down overlay (hidden by default) -->
  <div id="drillOverlay" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center">
    <div class="bg-light dark:bg-dark neumorphic w-11/12 md:w-2/3 p-6 rounded relative max-h-[90vh] overflow-auto">
      <button id="drillClose" class="absolute top-4 right-4 neu-btn" aria-label="Close details">
        <i data-feather="x"></i>
      </button>
      <h3 class="text-xl font-semibold mb-4">Detailed Drill-Down View</h3>
      <p class="text-sm mb-4">
        Here is the in-depth information—graphs, tables, logs, whatever your backend supplies.
      </p>
      <div class="h-40 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
        <span class="opacity-50">[Detailed Chart or Data Table]</span>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const overlay = document.getElementById('drillOverlay');
      const openBtn = document.getElementById('drillOpen');
      const closeBtn= document.getElementById('drillClose');

      function showOverlay() {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        feather.replace();
      }
      function hideOverlay() {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
      }

      openBtn.addEventListener('click', showOverlay);
      closeBtn.addEventListener('click', hideOverlay);
      // also close if clicking outside content
      overlay.addEventListener('click', e => {
        if (e.target === overlay) hideOverlay();
      });
    });
  </script>

  <!--
  Changelog:
  - Created CardDrilldown.php to demonstrate a modal drill-down overlay.
  - Overlay toggles between hidden/flex and binds open/close buttons.
  - Includes click-outside logic to close overlay.
  -->

<!--
Changelog:
- Updated to use shared card_header.php include.
-->
