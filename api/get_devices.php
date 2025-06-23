<?php
declare(strict_types=1);

// ───────────────────────────────────────────────
// DEBUG BLOCK (Always Keep at Top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ───────────────────────────────────────────────

/**
 * API  ▸  get_devices.php
 * ---------------------------------------------------------------
 * One-file endpoint that proxies to the upstream MPS Monitor
 *   POST  /Device/List
 *
 * Fallback logic added:
 *   • If caller omits PageRows, SortColumn or SortOrder, we inject
 *     safe defaults so the upstream API never rejects the payload
 *     and UI cards won’t silently return “0 devices” again.
 *   • Still honours CustomerCode or FilterDealerId exactly as sent.
 *
 * Hard requirements  (per AllEndpoints.json)
 *   PageNumber   int   | required
 *   PageRows     int   | required (default 15)
 *   SortColumn   str   | required (default "ExternalIdentifier")
 *   SortOrder    str   | required ("Asc"|"Desc", default "Asc")
 */

// ───── 1) Load .env and prep token  ─────────────────────────────
$env = parse_env_file(__DIR__ . '/../.env');

$token   = get_token($env);               // <— defined in shared helper
$apiBase = rtrim($env['API_BASE_URL'], '/') . '/Device/List';

// ───── 2) Read client JSON, merge fallbacks ────────────────────
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Always have PageNumber
$input['PageNumber'] = (int)($input['PageNumber'] ?? 1);

// Fallbacks
$input += [
    'PageRows'   => 15,
    'SortColumn' => 'ExternalIdentifier',
    'SortOrder'  => 'Asc',
];

// ───── 3) POST to upstream  ────────────────────────────────────
$ch = curl_init($apiBase);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_POSTFIELDS     => json_encode($input),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
]);
$raw = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Network error?
if ($raw === false) {
    http_response_code(502);
    echo json_encode([
        'IsValid' => false,
        'Errors'  => [['Code' => 'Curl', 'Description' => $err]],
    ]);
    exit;
}

// Forward JSON as-is
header('Content-Type: application/json');
echo $raw;
