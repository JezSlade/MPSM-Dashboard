<?php declare(strict_types=1);
/**
 * /cards/card_dashboard_counters.php
 *
 * Dashboard-level “counters” summary.
 * Calls  Dashboard/GetCounters  and renders the result through the
 * glass-morphic helper   /includes/card_bootstrap.php.
 *
 * ─────────────────────────────────────────────────────────────
 * CHANGELOG (2025-06-20 – counter card patch #3)
 * • Inject CustomerCode into payload (pulled from cookie fallback .env)
 * • Added CustomerCode to $requiredFields so bootstrap will call API
 * ─────────────────────────────────────────────────────────────
 */

/* ─── Resolve customer context ────────────────────────────── */

$customerCode = $_COOKIE['selectedCustomerCode']
             ?? getenv('DEFAULT_CUSTOMER_CODE')       // .env line
             ?? null;                                 // final fallback

/* ─── API definition ─────────────────────────────────────── */

$path           = 'Dashboard/GetCounters';
$requiredFields = ['CustomerCode'];            // bootstrap validates this
$payload        = ['CustomerCode' => $customerCode];

$useCache            = true;
$enableCacheRefresh  = false;

/* ─── UI metadata ─────────────────────────────────────────── */

$cardTitle = 'Dashboard Counters';

$columns = [
    'Name'  => 'Counter',
    'Count' => 'Value',
];

$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

/* ─── Kick it all off ─────────────────────────────────────── */

require __DIR__ . '/../includes/card_bootstrap.php';
