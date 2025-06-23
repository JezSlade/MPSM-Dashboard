<?php
declare(strict_types=1);

// ------------------------------------------------------------------
// DEBUG BLOCK (Always Keep at Top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ------------------------------------------------------------------

/**
 * Card: Device Counters (placeholder version)
 * ---------------------------------------------------------------
 * The original version tried to pull live data and broke the view
 * when JSON parsing failed.  This stub keeps layout intact while
 * we debug /api logic in isolation.
 *
 * When we’re ready to reactivate real data:
 *   1. Swap this stub out for the fully-featured card.
 *   2. Make sure its API endpoint returns clean JSON.
 */

// Card wrapper — matches the glass/neumorphic look
?>
<div class="card device-counters wip">
    <header>
        <h2>Device Counters <small>(Coming Soon)</small></h2>
    </header>

    <p class="info">
        This placeholder prevents JSON parse errors while we stabilise the
        <code>/api/device_counters.php</code> endpoint.<br>
        Check <code>/logs/debug.log</code> for live API traces.
    </p>
</div>

<style>
/* Minimal styling so the placeholder still looks like a card */
.card.device-counters {
    padding: 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    background: var(--bg-card, rgba(255,255,255,0.08));
    color: var(--text-dark, #f5f5f5);
}

.card.device-counters header {
    margin-bottom: .75rem;
}

.card.device-counters h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.card.device-counters small {
    font-weight: 400;
    opacity: .6;
}

.card.device-counters .info {
    font-size: .9rem;
    line-height: 1.4;
}
</style>
