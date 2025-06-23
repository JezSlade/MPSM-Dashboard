<?php
/**
 * Sandbox View
 * ------------------------------------------------------------------
 * Renders ONLY the card files listed in $activeCards so we can focus
 * on one feature at a time without touching the rest of the dashboard.
 *
 * How to use:
 *   1. Edit the $activeCards array below to point at exactly the card
 *      filenames you want live-rendered.
 *   2. Point index.php (or whatever router you use) to load
 *        render_view('views/sandbox.php');
 *
 * This file follows all MPSM CODE AUDIT PROTOCOL rules:
 *   • No external libs / composer
 *   • Manual path safety with __DIR__
 *   • Strict types, PHP 8.4+ compatible
 */

declare(strict_types=1);

// ------------------------------------------------------------------
// 1. List of active cards for the current sandbox session.
//    Comment/uncomment as needed:
$activeCards = [
    'card_devices.php',
    'card_dashboard_device_counters.php'
    // 'card_device_alerts.php',
];

// ------------------------------------------------------------------
// 2. Resolve the absolute /cards/ directory safely.
$cardsDir = realpath(__DIR__ . '/../cards');
if ($cardsDir === false) {
    echo '<p class="error">Cards directory not found.</p>';
    return;
}

// ------------------------------------------------------------------
// 3. Render chosen cards inside a responsive grid wrapper.
?>
<div id="sandbox" class="card-grid">
<?php
foreach ($activeCards as $cardFile) {
    $cardPath = $cardsDir . DIRECTORY_SEPARATOR . $cardFile;

    if (is_readable($cardPath)) {
        include $cardPath;
    } else {
        // Graceful fallback so the layout never breaks.
        ?>
        <div class="card placeholder">
            <h3><?php echo htmlspecialchars($cardFile); ?></h3>
            <p>Placeholder – file missing or not yet implemented.</p>
        </div>
        <?php
    }
}
?>
</div>

<style>
/* Lightweight grid so sandboxed cards stay tidy */
#sandbox.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
}

/* Very subtle placeholder styling (inherits glass/neumorphic vars) */
.card.placeholder {
    padding: 2rem;
    border-radius: 12px;
    backdrop-filter: blur(8px);
    background: var(--bg-card, rgba(255,255,255,0.08));
    color: var(--text-dark, #eee);
    text-align: center;
}
</style>
