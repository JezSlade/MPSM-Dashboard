<?php declare(strict_types=1);
/**
 * /cards/card_dashboard_counters.php
 *
 * Dashboard-level summary counters.
 * Hits  MPS Monitor  endpoint  Dashboard/GetCounters
 * and renders a two-column table via  card_bootstrap.php.
 *
 * ─────────────────────────────────────────────────────────────
 * CHANGELOG  (2025-06-20  –  counter card patch #2)
 * • Added  $useCache            (true)  → lets bootstrap decide
 * • Added  $enableCacheRefresh  (false) → no auto-refresh yet
 * • No other logic changes
 * ─────────────────────────────────────────────────────────────
 */

$path           = 'Dashboard/GetCounters';   // API route
$requiredFields = [];                        // none for this endpoint
$payload        = [];                        // bootstrap will inject CustomerCode
$useCache       = true;                      // read/write JSON cache
$enableCacheRefresh = false;                 // disable auto timer

/* ─── UI metadata ─────────────────────────────────────────── */

$cardTitle = 'Dashboard Counters';

/* Column map:  API field ➜ Pretty label */
$columns = [
    'Name'  => 'Counter',
    'Count' => 'Value',
];

$enableSearch     = false;   // usually only 5-10 rows
$enablePagination = false;
$pageSize         = 15;      // safety default

/* ─── Kick it all off ─────────────────────────────────────── */

require __DIR__ . '/../includes/card_bootstrap.php';
