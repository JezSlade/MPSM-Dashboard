<?php declare(strict_types=1);
/**
 * index.php  –  SPA root
 * Patch date: 2025-06-20
 *
 * • Adds a JSON gate so XHR/fetch callers don’t get the full HTML shell.
 * • Inherits the universal error/exception bridge (error_bootstrap.php).
 * • Leaves visual behaviour for human visitors untouched.
 */

/* ───────────── Unified error handling & debug setup ───────────── */
require_once __DIR__ . '/includes/error_bootstrap.php';

/* Optional: keep original log target but silence on-page notices */
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
ini_set('display_errors', '0');   // warnings now go to the log only

/* ───────────── Detect “JSON wanted” requests ────────────────────
 * Criteria:
 *   • X-Requested-With: XMLHttpRequest   AND
 *   • Accept header contains application/json
 *   •  – OR –   ?format=json query parameter
 */
$isJsonRequest =
    (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0)
        && strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
    )
    || (($_GET['format'] ?? '') === 'json');

if ($isJsonRequest) {
    header('Content-Type: application/json');
    http_response_code(400);   // Bad Request (caller picked wrong endpoint)
    echo json_encode([
        'error' => 'index.php serves the HTML SPA.',
        'hint'  => 'Call one of the /api/*.php endpoints for JSON.',
        'path'  => $_SERVER['REQUEST_URI'] ?? '',
    ], JSON_THROW_ON_ERROR);
    exit;
}

/* ───────────── Normal HTML workflow continues below ───────────── */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
