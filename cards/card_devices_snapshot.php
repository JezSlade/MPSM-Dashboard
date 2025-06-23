<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // unified debug helper

/**
 * Card ▸ Devices Snapshot
 * ------------------------------------------------------------------
 * • Pure-PHP: no JS.
 * • “Devices Online (n)” badge – click toggles table.
 * • Posts to /api/get_devices.php (wrapper for /Device/List).
 * • Uses render_table() from includes/table_helper.php; defines a
 *   fallback if the helper is ever missing.
 */

/*───────────────────────────────────────────────────────────
 | 1  Ensure render_table() exists
 *───────────────────────────────────────────────────────────*/
$helper = __DIR__ . '/../includes/table_helper.php';
if (is_readable($helper)) {
    require_once $helper;
}
if (!function_exists('render_table')) {
    function render_table(array $headers, array $rows): string
    {
        $h = '<table class="ds-fallback"><thead><tr>';
        foreach ($headers as $label) {
            $h .= '<th>' . htmlspecialchars($label) . '</th>';
        }
        $h .= '</tr></thead><tbody>';
        if (!$rows) {
            $h .= '<tr><td colspan="' . count($headers) . '">No data</td></tr>';
        } else {
            foreach ($rows as $row) {
                $h .= '<tr>';
                foreach ($headers as $field => $_) {
                    $h .= '<td>' . htmlspecialchars((string)($row[$field] ?? '')) . '</td>';
                }
                $h .= '</tr>';
            }
        }
        return $h . '</tbody></table>';
    }
}

/*───────────────────────────────────────────────────────────
 | 2  Build API request body
 *───────────────────────────────────────────────────────────*/
$customer = $_SESSION['selectedCustomer'] ?? null;
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

$pageSize = 15;
$page     = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;

$body = [
    'PageNumber' => $page,
    'PageRows'   => $pageSize,
    'SortColumn' => 'ExternalIdentifier',   // ✔ valid column
    'SortOrder'  => 'Asc',
];
if ($customer) {
    $body['CustomerCode'] = $customer;
} else {
    $body['FilterDealerId'] = $dealerId;
}

/*───────────────────────────────────────────────────────────
 | 3  POST to local API wrapper
 *───────────────────────────────────────────────────────────*/
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
$response = curl_exec($ch);
curl_close($ch);

$data = $response ? json_decode($response, true) : null;
$total = ($data['IsValid'] ?? false) ? ($data['TotalRows'] ?? 0) : 0;
$rows  = ($data['IsValid'] ?? false) ? ($data['Result']    ?? []) : [];

/*───────────────────────────────────────────────────────────
 | 4  Pagination + helpers
 *───────────────────────────────────────────────────────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $pageSize));

function self_url(bool $exp, int $p = 1): string
{
    $q = ['view' => 'sandbox'];
    if ($exp) {
        $q['ds_exp']  = 1;
        $q['ds_page'] = $p;
    }
    return '/index.php?' . http_build_query($q);
}

/* column headers */
$headers = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model',
    'IpAddress'          => 'IP Address',
    'Department'         => 'Department',
];
?>
<!-- ─────────── Card Mark-up ─────────── -->
<div class="card devices-snapshot">
    <h2 style="margin:0;font-size:1.25rem;font-weight:700">
        <a href="<?= htmlspecialchars(self_url(!$expanded)); ?>"
           style="text-decoration:none;color:inherit">
            Devices Online <span class="badge"><?= $total; ?></span>
        </a>
    </h2>

<?php if ($expanded): ?>
    <?= render_table($headers, $rows); ?>

    <div class="pagination" style="text-align:center;margin-top:1rem">
        <?php if ($page > 1): ?>
            <a href="<?= htmlspecialchars(self_url(true, $page - 1)); ?>">&larr; Prev</a>
        <?php endif; ?>
        <span><?= $page; ?> / <?= $totalPages; ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="<?= htmlspecialchars(self_url(true, $page + 1)); ?>">Next &rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<style>
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));
    color:var(--text-dark,#f5f5f5)
}
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
</style>
