<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';

/*───────────────────────────────────────────────────────────
 | 0) SESSION + FILTER VARS
 *───────────────────────────────────────────────────────────*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customer = $_SESSION['selectedCustomer'] ?? '';
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

/*───────────────────────────────────────────────────────────
 | 1) BUILD & LOG REQUEST BODY  (SortOrder → Desc)
 *───────────────────────────────────────────────────────────*/
$pageSize = 15;
$page     = max(1, (int)($_GET['ds_page'] ?? 1));

$body = [
    'PageNumber'        => $page,
    'PageRows'          => $pageSize,
    'SortColumn'        => 'ExternalIdentifier',
    'SortOrder'         => 'Desc',           // ↘ Now descending
    'FilterDealerId'    => $dealerId,
    'DeviceType'        => 'Printer',
];

if ($customer !== '') {
    $body['FilterCustomerCodes'] = [$customer];
}

error_log('[devices_snapshot] Request body: ' . json_encode($body));

/*───────────────────────────────────────────────────────────
 | 2) POST to API WRAPPER
 *───────────────────────────────────────────────────────────*/
$apiUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
        . $_SERVER['HTTP_HOST']
        . '/api/get_devices.php';

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

$data  = $response ? json_decode($response, true) : null;
$total = ($data['IsValid'] ?? false) ? ($data['TotalRows'] ?? 0) : 0;
$raw   = ($data['IsValid'] ?? false) ? ($data['Result']    ?? []) : [];

/*───────────────────────────────────────────────────────────
 | 3) NORMALISE ROWS (ID merge unchanged)
 *───────────────────────────────────────────────────────────*/
$rows = [];
foreach ($raw as $r) {
    $asset = trim((string)($r['AssetNumber'] ?? ''));
    $ext   = trim((string)($r['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;

    $rows[] = [
        'Drill'      => $r['DeviceId'] ?? $r['Id'] ?? '',
        'Identifier' => $id,
        'Department' => $r['Department'] ?? '',
        'Note'       => $r['Note'] ?? $r['Notes'] ?? '',
    ];
}

/*───────────────────────────────────────────────────────────
 | 4) PAGINATION HELPERS
 *───────────────────────────────────────────────────────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $pageSize));

function self_url(bool $exp, int $p = 1): string
{
    $q = ['view'=>'dashboard'];
    if ($exp) {
        $q['ds_exp']  = 1;
        $q['ds_page'] = $p;
    }
    return '/index.php?' . http_build_query($q);
}

/*───────────────────────────────────────────────────────────
 | 5) RENDER CARD
 *───────────────────────────────────────────────────────────*/
?>
<div class="card devices-snapshot">
    <h2 style="margin:0;font-size:1.25rem;font-weight:700">
        <a href="<?= htmlspecialchars(self_url(!$expanded)); ?>"
           style="text-decoration:none;color:inherit">
            Devices Online <span class="badge"><?= $total; ?></span>
        </a>
    </h2>

    <?php if ($expanded): ?>
        <table class="snap">
            <thead>
                <tr>
                    <th></th>
                    <th>Asset&nbsp;/&nbsp;Ext&nbsp;ID</th>
                    <th>Department</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4">No data</td></tr>
            <?php else: foreach ($rows as $r):
                $link = '/index.php?view=device_detail&id=' . urlencode($r['Drill']);
            ?>
                <tr>
                    <td><a href="<?= htmlspecialchars($link); ?>" title="View detail">🔍</a></td>
                    <td><?= htmlspecialchars($r['Identifier']); ?></td>
                    <td><?= htmlspecialchars($r['Department']); ?></td>
                    <td><?= htmlspecialchars($r['Note']); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

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
.card.devices-snapshot {
    padding:1.2rem;border-radius:12px;backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));color:var(--text-dark,#f5f5f5);
}
.badge {
    display:inline-block;min-width:44px;text-align:center;padding:.2rem .5rem;
    border-radius:9999px;background:var(--bg-light,#2d8cff);
    color:#fff;font-weight:600;font-size:0.85rem;
}
/* keep compact table styling */
.snap {font-size:0.85rem;width:100%;border-collapse:collapse;margin-top:1rem}
.snap th, .snap td {padding:.4rem .6rem;text-align:left}
.snap thead tr {background:rgba(255,255,255,.1);font-weight:600}
.snap tbody tr:nth-child(even) {background:rgba(255,255,255,.05)}
.pagination a {margin:0 .4rem;color:var(--text-dark,#aaddff);text-decoration:none;font-size:0.85rem}
</style>
