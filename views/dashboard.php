<?php
// /views/dashboard.php
// Dashboard View: renders all cards in a responsive grid, auto‐discovering cards

declare(strict_types=1);

// (Header, navigation, and debug setup are handled in index.php)

// Capture any selected customer from query string (optional; cards read from cookie anyway)
$selectedCustomer = $_GET['customer'] ?? null;
?>
<main>
  <!-- Responsive card grid -->
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
    // Auto‐include every PHP file in /cards/, except Base helpers or non‐cards
    $cardsDir = __DIR__ . '/../cards/';
    foreach (scandir($cardsDir, SCANDIR_SORT_ASCENDING) as $file) {
        if (
            $file === '.' ||
            $file === '..' ||
            pathinfo($file, PATHINFO_EXTENSION) !== 'php' ||
            preg_match('/Base\.php$/i', $file) // skip any *Base.php helpers
        ) {
            continue;
        }
        include $cardsDir . $file;
    }
    ?>
  </div>
</main>

<!-- Client‐side behavior for sorting, expand/collapse, drilldown, and customer‐row clicks -->
<script src="/public/js/card-interactions.js"></script>
