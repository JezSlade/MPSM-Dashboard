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
 * Devices Snapshot (stable POST edition)
 * ------------------------------------------------------------------
 * • Sends POST /api/get_devices.php (wrapper to /Device/List) with
 *   JSON body { PageNumber, PageRows, CustomerCode | FilterDealerId }.
 * • Uses render_table() from /includes/table_helper.php; if missing
 *   we declare a tiny fallback so the card never throws.
 * • Click header to toggle expansion; pure PHP, no JS.
 */

// ───── 0)  Ensure render_table() exists ───────────────────────────
$helper = __DIR__ . '/../includes/table_helper.php';
if (is_readable($helper)) {
    require_once $helper;
}

if (!function_exists('render_table')) {
    function render_table(array $headers, array $rows): string
    {
        $html  = '<table class="ds-fallback"><thead><tr>';
        foreach ($headers as $label) {
            $html .= '<th>' . htmlspecialchars($label) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if (!$rows) {
            $html .= '<tr><td colspan="' . count($headers) . '">No data</td></tr>';
        } else {
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($headers as $field => $_) {
                    $html .= '<td>' . htmlspecialchars((string)($row[$field] ?? '')) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table><style>
.ds-fallback{width:100%;border-collapse:collapse;margin-top:1rem}
.ds-fallback th,.ds-fallback td{padding:.5rem .75rem;text-align:left}
.ds-fallback thead tr{background:rgba(255,255,255,.1);font-weight:600}
.ds-fallback tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
</style>';
        return $html;
    }
}

// ───── 1)  Build request body ────────────────────────────────────
$customerCode = $_SESSION['selectedCustomer'] ?? null;
$dealerId     = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

$pageSize    = 15;
$currentPage = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;

$body = [
    'PageNumber' => $currentPage,
    'PageRows'   => $pageSize,
    'SortColumn' => 'ExternalIdentifier',   // ← change this line
    'SortOrder'  => 'Asc',
];
if ($customerCode) {
    $body['CustomerCode'] = $customerCode;
} else {
    $body['FilterDealerId'] = $dealerId;
}

// ───── 2)  Execute POST via cURL ─────────────────────────────────
$apiUrl =
    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
    $_SERVER['HTTP_HOST'] .
    '/api/get_devices.php';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);
$raw  = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

if ($raw === false) {
    error_log("[devices_snapshot] CURL error: $err");
    $json = null;
} else {
    $json = json_decode($raw, true);
}

// ───── 3)  Parse response ───────────────────────────────────────
$totalRows = ($json['IsValid'] ?? false) ? ($json['TotalRows'] ?? 0) : 0;
$rows      = ($json['IsValid'] ?? false) ? ($json['Result']    ?? []) : [];

$isExpanded  = isset($_GET['ds_exp']);
$totalPages  = max(1, (int)ceil($totalRows / $pageSize));

function self_url(bool $expand, int $page = 1): string
{
    $p = ['view' => 'sandbox'];
    if ($expand) {
        $p['ds_exp']  = '1';
        $p['ds_page'] = $page;
    }
    return '/index.php?' . http_build_query($p);
}

// headers for table_helper
$tableHeaders = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model',
    'IpAddress'          => 'IP Address',
    'Department'         => 'Department',
];
?>
<!-- ────────── CARD MARK-UP ────────── -->
<div class="card devices-snapshot">
    <header>
        <h2>
            <a href="<?= htmlspecialchars(self_url(!$isExpanded)); ?>">
                Devices Online <span class="badge"><?= $totalRows; ?></span>
