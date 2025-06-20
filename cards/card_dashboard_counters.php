<?php declare(strict_types=1);
/**
 *  /cards/card_dashboard_counters.php
 *
 *  High-level “counters” summary shown on the main dashboard.
 *  Calls the MPS Monitor endpoint  **Dashboard/GetCounters**  and
 *  renders its response with the glass-morphic table wrapper that
 *  lives in  /includes/card_bootstrap.php.
 *
 *  ─────────────────────────────────────────────────────────────
 *  WHY THIS PATCH?
 *  • Replaces the old   api_bootstrap.php   include (which echoed
 *    raw JSON and could fatal-exit) with   card_bootstrap.php
 *    so the card now fits the SPA’s UI layer and can’t break the
 *    whole page on a warning.
 *  • Adds the metadata variables that the bootstrap expects
 *    ($cardTitle, $columns, pager/search toggles).
 *  • Leaves the card in the   /cards/   folder—no relocations.
 *  ─────────────────────────────────────────────────────────────
 */

$path           = 'Dashboard/GetCounters';   // API route
$requiredFields = [];                        // none; helper injects CustomerCode
$payload        = [];                        // additional params (none)

/* ───── UI metadata ────────────────────────────────────────── */

$cardTitle = 'Dashboard Counters';

/**
 * Column map:  API field ➜ Pretty table label
 * If the endpoint returns extra keys they’re simply ignored;
 * missing keys render blank cells.
 */
$columns = [
    'Name'  => 'Counter',   // e.g. “Total Pages”
    'Count' => 'Value',     // e.g. “123 456”
];

$enableSearch     = false;  // usually only a handful of rows
$enablePagination = false;  // ditto; turn on later if needed
$pageSize         = 15;     // safety default

/* ───── Bootstrap ──────────────────────────────────────────── */

require __DIR__ . '/../includes/card_bootstrap.php';
