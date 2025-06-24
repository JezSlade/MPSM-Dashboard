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

<!-- Client-side behavior for sorting, expand/collapse, drilldowns, and slide-out -->
<script src="/public/js/card-interactions.js"></script>
