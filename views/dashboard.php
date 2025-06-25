<?php
declare(strict_types=1);
// /views/dashboard.php — Auto-discover and render all cards in a responsive grid
?>
<main class="flex-1 overflow-y-auto container space-y-6">
  <div id="cardGrid" class="card-grid">
    <?php
    $cardsDir = __DIR__ . '/../cards/';
    foreach (scandir($cardsDir, SCANDIR_SORT_ASCENDING) as $file) {
        if (
            $file === '.' ||
            $file === '..' ||
            pathinfo($file, PATHINFO_EXTENSION) !== 'php' ||
            preg_match('/Base\.php$/i', $file)    // skip any Base files
        ) {
            continue;
        }
        include $cardsDir . $file;
    }
    ?>
  </div>
</main>
