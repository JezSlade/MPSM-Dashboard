<?php
/**
 * cards/CardDrilldown.php — Dummy card demonstrating a drill-down modal overlay
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file
?>
<div class="neumorphic p-4">
<?php
// $title = 'CardDrilldown'; // REMOVED (managed by index.php)
// $cardId = 'CardDrilldown'; // REMOVED (managed by index.php)
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Drill-Down Card</h2>
    <button id="drillOpen-<?php echo $cardId; ?>" class="neu-btn" aria-label="Drill down into card"> <i data-feather="eye"></i>
    </button>
  </header>
  <p class="text-sm opacity-75">
    Click the eye icon to drill down into detailed view.
  </p>

  <div id="drillOverlay-<?php echo $cardId; ?>" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center"> <div class="bg-light dark:bg-dark neumorphic w-11/12 md:w-2/3 p-6 rounded relative max-h-[90vh] overflow-auto">
      <button id="drillClose-<?php echo $cardId; ?>" class="absolute top-4 right-4 neu-btn" aria-label="Close details"> <i data-feather="x"></i>
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
      // Use the unique IDs based on $cardId
      const overlay = document.getElementById('drillOverlay-<?php echo $cardId; ?>');
      const openBtn = document.getElementById('drillOpen-<?php echo $cardId; ?>');
      const closeBtn= document.getElementById('drillClose-<?php echo $cardId; ?>');

      if (!overlay || !openBtn || !closeBtn) {
          console.warn('Drilldown Card: Elements not found for card ID: <?php echo $cardId; ?>');
          return;
      }

      function showOverlay() {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        if (typeof feather !== 'undefined') { // Ensure feather is available
          feather.replace();
        }
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