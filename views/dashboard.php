<?php declare(strict_types=1);
require_once __DIR__.'/../includes/header.php';           // top bar, icons
require_once __DIR__.'/../includes/card_loader.php';      // NEW sandbox

/* ─── Gather card list & user prefs ────────────────────── */

$cardsDir   = __DIR__ . '/../cards/';
$allCards   = array_map('basename', glob($cardsDir . 'card_*.php'));   // every card file

// Helper that merges cookie/localStorage prefs with full list
require_once __DIR__.'/../includes/preferences.php';     // contains getVisibleCards()
$visibleCards = getVisibleCards($allCards);

/* ─── Render main area ─────────────────────────────────── */

echo '<main id="dashboard-view" class="dashboard-grid">';

foreach ($visibleCards as $card) {
    // Every card is now sandboxed ➜ any warning becomes a red box
    echo render_card($cardsDir . $card);
}

echo '</main>';

require_once __DIR__.'/../includes/footer.php';          // footer text
