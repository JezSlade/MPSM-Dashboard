<?php declare(strict_types=1);

/*
 * Dashboard main view
 * ───────────────────
 * • Shows header, nav icons, customer pill
 * • Renders each selected card through the sandbox loader
 * • Footer always prints, even if a card explodes
 */

require_once __DIR__ . '/../includes/header.php';          // top bar

/* –– NEW: load sandbox & preference helpers –––––––––––––– */
require_once __DIR__ . '/../includes/card_loader.php';
require_once __DIR__ . '/../includes/preferences.php';

/* –– Discover cards on disk –––––––––––––––––––––––––––––– */
$cardsDir = __DIR__ . '/../cards/';
$allCards = array_map('basename', glob($cardsDir . 'card_*.php'));

/* –– Merge with user prefs stored in cookie –––––––––––––– */
$visibleCards = getVisibleCards($allCards);

/* –– Main viewport –––––––––––––––––––––––––––––––––––––– */
echo '<main id="dashboard-view" class="dashboard-grid">';

if ($visibleCards === []) {
    echo '<p style="
            color:var(--text-dark);
            opacity:.7;
            margin:2rem auto;
            font-style:italic;
            text-align:center;
         ">
            No cards selected.<br>
            Click the purple gear to choose some.
         </p>';
} else {
    foreach ($visibleCards as $card) {
        /*  Every card runs inside the sandbox:
         *  – warnings/notices → Throwable
         *  – logger writes to /logs/debug.log
         *  – red ⚠ placeholder returned here
         *  The footer below ALWAYS prints.
         */
        echo render_card($cardsDir . $card);
    }
}

echo '</main>';

/* –– Sticky footer ––––––––––––––––––––––––––––––––––––––– */
require_once __DIR__ . '/../includes/footer.php';
