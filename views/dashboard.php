<?php
// /views/dashboard.php — Auto-discover and render all cards in a responsive grid
declare(strict_types=1);
?>
<main class="flex-1 overflow-y-auto p-4 space-y-6">
  <div
    id="cardGrid"
    class="card-grid"
    style="
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
    "
  >
    <?php
    // Automatically include every card in /cards/, skipping Base helpers
    $cardsDir = __DIR__ . '/../cards/';
    foreach (scandir($cardsDir, SCANDIR_SORT_ASCENDING) as $file) {
        if (
            $file === '.' ||
            $file === '..' ||
            pathinfo($file, PATHINFO_EXTENSION) !== 'php' ||
            preg_match('/Base\.php$/i', $file)    // skip any Base helper files
        ) {
            continue;
        }
        include $cardsDir . $file;
    }
    ?>
  </div>
</main>

<!-- Settings Modal -->
<div id="settings-modal" class="modal hidden">
  <div class="modal-content">
    <h2>Dashboard Settings</h2>
    <p>You can configure card preferences here.</p>
    <button class="icon-button close-settings" title="Close">
      <i data-feather="x"></i> Close
    </button>
  </div>
</div>

<!-- Client-side behavior -->
<script src="/public/js/card-interactions.js"></script>
<script>
  // Redundant fallback in case card JS loads before UI init
  if (typeof initializeGlobalUI === 'function') {
    initializeGlobalUI();
  }
</script>
