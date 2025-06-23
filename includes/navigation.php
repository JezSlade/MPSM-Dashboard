<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/debug.php';

/* 1. SESSION / default customer ---------------------------------- */
session_start();
$customer = $_SESSION['selectedCustomer'] ?? '';           // '' means none
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

/* 2. Build request body ------------------------------------------ */
$size = 15;
$page = max(1, (int)($_GET['ds_page'] ?? 1));

$body = [
    'PageNumber'     => $page,
    'PageRows'       => $size,
    'SortColumn'     => 'ExternalIdentifier',
    'SortOrder'      => 'Asc',
    'FilterDealerId' => $dealerId,            // ALWAYS send dealer
    'DeviceType'     => 'Printer',            // limit to printers only
];

if ($customer !== '') {
    $body['CustomerCode'] = $customer;        // restrict to that customer
}

/* 3. POST to wrapper --------------------------------------------- */
$api = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
     . $_SERVER['HTTP_HOST'] . '/api/get_devices.php';

$ch = curl_init($api);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);
$json = json_decode(curl_exec($ch) ?: '', true);
curl_close($ch);

$total = ($json['IsValid'] ?? false) ? ($json['TotalRows'] ?? 0) : 0;
$raw   = ($json['IsValid'] ?? false) ? ($json['Result']    ?? []) : [];

/* 4. Normalise rows ---------------------------------------------- */
$rows=[];
foreach($raw as $r){
    $asset = trim((string)($r['AssetNumber'] ?? ''));
    $extId = trim((string)($r['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $extId;      // show one only

    $rows[] = [
        'Drill'      => $r['DeviceId'] ?? $r['Id'] ?? '',
        'Identifier' => $id,
        'Department' => $r['Department'] ?? '',
        'Note'       => $r['Note'] ?? $r['Notes'] ?? '',
    ];
}

/* 5. Pagination helpers ------------------------------------------ */
$expanded   = isset($_GET['ds_exp']);
$pages      = max(1, (int)ceil($total / $size));

function link_self(bool $exp, int $p = 1): string
{
    $q=['view'=>'dashboard'];
    if ($exp) $q += ['ds_exp'=>1,'ds_page'=>$p];
    return '/index.php?'.http_build_query($q);
}

/* 6. Render ------------------------------------------------------- */
?>
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?=htmlspecialchars(link_self(!$expanded));?>" style="text-decoration:none;color:inherit">
        Devices Online <span class="badge"><?=$total;?></span>
    </a>
  </h2>

<?php if($expanded): ?>
  <table class="snap"><thead><tr>
      <th></th><th>Asset / Ext&nbsp;ID</th><th>Department</th><th>Note</th>
  </tr></thead><tbody>
  <?php if(!$rows): ?>
      <tr><td colspan="4">No data</td></tr>
  <?php else: foreach($rows as $r):
        $detail='/index.php?view=device_detail&id='.urlencode($r['Drill']); ?>
      <tr>
        <td><a href="<?=htmlspecialchars($detail);?>" title="Detail">🔍</a></td>
        <td><?=htmlspecialchars($r['Identifier']);?></td>
        <td><?=htmlspecialchars($r['Department']);?></td>
        <td><?=htmlspecialchars($r['Note']);?></td>
      </tr>
  <?php endforeach; endif; ?>
  </tbody></table>

  <div class="pagination" style="text-align:center;margin-top:1rem">
    <?php if($page>1): ?>
      <a href="<?=htmlspecialchars(link_self(true,$page-1));?>">&larr; Prev</a>
    <?php endif;?>
    <span><?=$page;?> / <?=$pages;?></span>
    <?php if($page<$pages): ?>
      <a href="<?=htmlspecialchars(link_self(true,$page+1));?>">Next &rarr;</a>
    <?php endif;?>
  </div>
<?php endif;?>
</div>

<style>
.card.devices-snapshot{padding:1.5rem;border-radius:12px;backdrop-filter:blur(10px);
  background:var(--bg-card,rgba(255,255,255,.08));color:var(--text-dark,#f5f5f5)}
.badge{display:inline-block;min-width:48px;text-align:center;padding:.2rem .6rem;border-radius:9999px;
  background:var(--bg-light,#2d8cff);color:#fff;font-weight:600}
.snap{width:100%;border-collapse:collapse;margin-top:1rem}
.snap th,.snap td{padding:.5rem .75rem;text-align:left}
.snap thead tr{background:rgba(255,255,255,.1);font-weight:600}
.snap tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
</style>
