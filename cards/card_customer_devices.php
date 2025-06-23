<?php
declare(strict_types=1);

// ─── Bootstrap & API Client ───────────────────────────────────
require_once __DIR__ . '/../includes/api_client.php';
$env = load_env(__DIR__ . '/../.env');

// ─── 1) SESSION & CUSTOMER ───────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customerCode = $_SESSION['selectedCustomer'] ?? '';
if ($customerCode === '') {
    echo '<p class="error">No customer selected.</p>';
    return;
}

// ─── 2) PAGINATION PARAMETERS ────────────────────────────────
$page    = isset($_GET['page'])    ? max(1, (int)$_GET['page'])    : 1;
$perPage = isset($_GET['perPage']) ? max(1, (int)$_GET['perPage']) : 15;

// ─── 3) CALL /Device/List ────────────────────────────────────
$requestBody = [
    'request' => [
        'DealerCode'   => $env['DEALER_CODE']   ?? '',
        'CustomerCode' => $customerCode,
        'PageNumber'   => $page,
        'PageRows'     => $perPage,
        'SortColumn'   => 'ExternalIdentifier',
        'SortOrder'    => 'Asc',
        // you can add 'DeviceType'=>'Printer' here if needed
    ]
];
error_log('[cust_devices] Device/List Request: '.json_encode($requestBody));

$resp = api_call(
    $env,
    'POST',
    '/Device/List',
    $requestBody
);

// ─── 4) EXTRACT RESULTS ──────────────────────────────────────
$total   = (!empty($resp['IsValid'])) 
         ? (int)($resp['Result']['TotalCount'] ?? 0) 
         : 0;

$devices = (!empty($resp['IsValid'])) 
         ? ($resp['Result']['Items'] ?? []) 
         : [];

// ─── 5) NORMALIZE ROWS ──────────────────────────────────────
$rows = [];
foreach ($devices as $d) {
    $asset = trim((string)($d['AssetNumber']        ?? ''));
    $ext   = trim((string)($d['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;
    $dept  = $d['Department'] ?? $d['OfficeId'] ?? '';
    $note  = $d['Note']       ?? $d['Notes']    ?? '';
    $rows[] = [
        'Identifier' => $id,
        'Department' => $dept,
        'Note'       => $note,
    ];
}

// ─── 6) RENDER CARD ─────────────────────────────────────────
?>
<div class="card customer-devices">
  <header>
    <h2 style="margin:0;font-size:1.25rem;font-weight:700">
      Customer Devices <span class="badge"><?= $total ?></span>
    </h2>
  </header>

  <table class="snap">
    <thead>
      <tr>
        <th>Asset&nbsp;/&nbsp;Ext&nbsp;ID</th>
        <th>Department</th>
        <th>Note</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="3">No data</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['Identifier']) ?></td>
          <td><?= htmlspecialchars($r['Department']) ?></td>
          <td><?= htmlspecialchars($r['Note']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <?php if ($total > $perPage): 
      $last = (int)ceil($total / $perPage);
  ?>
  <nav class="pagination">
    <?php for ($i = 1; $i <= $last; $i++): ?>
      <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $i ?>&perPage=<?= $perPage ?>"
         class="<?= $i === $page ? 'active' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </nav>
  <?php endif; ?>
</div>

<style>
.card.customer-devices {
    padding:1.2rem; border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));
    color:var(--text-dark,#f5f5f5);
    margin-bottom:1rem;
}
.badge {
    display:inline-block; min-width:44px; text-align:center;
    padding:.2rem .5rem; border-radius:9999px;
    background:var(--bg-light,#2d8cff); color:#fff;
    font-weight:600; font-size:0.85rem;
}
.snap {
    font-size:0.85rem; width:100%; border-collapse:collapse;
    margin-top:.75rem;
}
.snap th, .snap td { padding:.4rem .6rem; text-align:left; }
.snap thead tr { background:rgba(255,255,255,.1); font-weight:600; }
.snap tbody tr:nth-child(even) { background:rgba(255,255,255,.05); }

.pagination {
    margin-top:1rem; text-align:center;
}
.pagination a {
    display:inline-block; margin:0 .25rem; padding:.25rem .5rem;
    border-radius:4px; background:rgba(255,255,255,.1);
    text-decoration:none; color:inherit;
}
.pagination a.active {
    background:rgba(255,255,255,.2); font-weight:600;
}
</style>
