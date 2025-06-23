<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // unified debug helper

/**
 * Devices Snapshot  – stable POST edition
 * ------------------------------------------------------------------
 * • Posts to /api/get_devices.php which forwards to /Device/List.
 * • Sends all required fields, including the VALID sort column
 *   DeviceId, so the API returns rows.
 * • Uses render_table() from includes/table_helper.php; if that
 *   helper is ever missing, defines a minimal fallback.
 * • Pure PHP, no JavaScript.
 */

/*──────────────────────────────────────────────────────────────────
 | 1) Ensure render_table helper
 *─────────────────────────────────────────────────────────────────*/
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

/*──────────────────────────────────────────────────────────────────
 | 2) Build request body
 *─────────────────────────────────────────────────────────────────*/
$customerCode = $_SESSION['selectedCustomer'] ?? null;
$dealerId     = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

$pageSize    = 15;
$currentPage = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;

$body = [
    'PageNumber' => $currentPage,
    'PageRows'   => $pageSize,
    'SortColumn' => 'DeviceId',   // VALID column → rows returned
    'SortOrder'  => 'Asc',
];
if ($customerCode) {
    $body['CustomerCode'] = $customerCode;
} else {
    $body['FilterDealerId'] = $dealerId;
}

/*──────────────────────────────────────────────────────────────────
 | 3) POST to local API wrapper
 *─────────────────────────────────────────────────────────────────*/
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

$json = $raw ? json_decode($raw, true) : null;
if ($raw === false || !($json['IsValid'] ?? false)) {
    error_log('[devices_snapshot] API error: ' . ($err ?: $raw));
    $totalRows = 0;
    $rows      = [];
} else {
    $totalRows = $json['TotalRows'] ?? 0;
    $rows      = $json['Result']    ?? [];
}

/*──────────────────────────────────────────────────────────────────
 | 4) Pagination helpers
 *─────────────────────────────────────────────────────────────────*/
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

/* headers for the table */
$tableHeaders = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model',
    'IpAddress'          => 'IP Address',
    'Department'         => 'Department',
];
?>
<!-- ────────── CARD ────────── -->
<div class="card devices-snapshot">
    <header>
        <h2>
            <a href="<?= htmlspecialchars(self_url(!$isExpanded)); ?>">
                Devices Online <span class="badge"><?= $totalRows; ?></span>
            </a>
        </h2>
    </header>

<?php if ($isExpanded): ?>
    <?= render_table($tableHeaders, $rows); ?>

    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="<?= htmlspecialchars(self_url(true, $currentPage - 1)); ?>">&larr; Prev</a>
        <?php endif; ?>
        <span><?= $currentPage; ?> / <?= $totalPages; ?></span>
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= htmlspecialchars(self_url(true, $currentPage + 1)); ?>">Next &rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<style>
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,0.08));
    color:var(--text-dark,#f5f5f5)
}
.card.devices-snapshot header a{color:inherit;text-decoration:none}
.card.devices-snapshot h2{margin:0;font-size:1.25rem;font-weight:700}
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.pagination{text-align:center;margin-top:1rem}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
.pagination span{margin:0 .5rem}
</style>
