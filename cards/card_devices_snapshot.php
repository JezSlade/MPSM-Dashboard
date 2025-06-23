<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // unified DEBUG

/*──────── 1. FETCH DEVICES (same wrapper as before) ────────*/
$customer = $_SESSION['selectedCustomer'] ?? null;
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';
$pageSize = 15;
$page     = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;

$body = [
  'PageNumber' => $page,
  'PageRows'   => $pageSize,
  'SortColumn' => 'ExternalIdentifier',
  'SortOrder'  => 'Asc',
] + ($customer ? ['CustomerCode'=>$customer] : ['FilterDealerId'=>$dealerId]);

$api = (isset($_SERVER['HTTPS'])?'https://':'http://') .
       $_SERVER['HTTP_HOST'].'/api/get_devices.php';

$ch = curl_init($api);
curl_setopt_array($ch,[
  CURLOPT_POST=>true,
  CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
  CURLOPT_POSTFIELDS=>json_encode($body),
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_TIMEOUT=>10
]);
$json = json_decode(curl_exec($ch) ?: '', true); curl_close($ch);

$total = ($json['IsValid'] ?? false) ? ($json['TotalRows'] ?? 0) : 0;
$rawRows = ($json['IsValid'] ?? false) ? ($json['Result'] ?? []) : [];

/*──────── 2. NORMALISE ROWS TO REQUIRED COLUMNS ───────────*/
$rows = [];
foreach ($rawRows as $r) {
    $asset  = $r['AssetNumber']        ?? '';
    $extId  = $r['ExternalIdentifier'] ?? '';
    $merged = $asset && $extId ? "$asset ($extId)" : ($asset ?: $extId);

    $rows[] = [
        'Drill'      => $r['DeviceId'] ?? $r['Id'] ?? '',  // primary key for link
        'Identifier' => $merged,
        'Department' => $r['Department'] ?? '',
        'Note'       => $r['Note'] ?? $r['Notes'] ?? '',
    ];
}

/*──────── 3. PAGINATION HELPERS ───────────────────────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $pageSize));

function self_url(bool $exp, int $p=1): string {
    $q=['view'=>'sandbox'];
    if ($exp) {$q+=['ds_exp'=>1,'ds_page'=>$p];}
    return '/index.php?'.http_build_query($q);
}

/*──────── 4. TABLE RENDER (custom for the 4 cols) ─────────*/
function render_snapshot_table(array $rows): string {
    $out = '<table class="snap"><thead><tr>'
         . '<th></th><th>Asset&nbsp;/&nbsp;Ext ID</th><th>Department</th><th>Note</th>'
         . '</tr></thead><tbody>';

    if (!$rows) {
        $out .= '<tr><td colspan="4">No data</td></tr>';
    } else {
        foreach ($rows as $r) {
            $link = '/index.php?view=device_detail&id='.urlencode($r['Drill']);
            $out .= '<tr>'
                  . '<td><a href="'.htmlspecialchars($link).'" title="View detail">🔍</a></td>'
                  . '<td>'.htmlspecialchars($r['Identifier']).'</td>'
                  . '<td>'.htmlspecialchars($r['Department']).'</td>'
                  . '<td>'.htmlspecialchars($r['Note']).'</td>'
                  . '</tr>';
        }
    }
    return $out.'</tbody></table>';
}
?>
<!--──────── CARD ────────-->
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?=htmlspecialchars(self_url(!$expanded));?>" style="text-decoration:none;color:inherit">
        Devices Online <span class="badge"><?=$total;?></span>
    </a>
  </h2>

<?php if ($expanded): ?>
  <?= render_snapshot_table($rows); ?>

  <div class="pagination" style="text-align:center;margin-top:1rem">
    <?php if ($page > 1): ?>
      <a href="<?=htmlspecialchars(self_url(true,$page-1));?>">&larr; Prev</a>
    <?php endif;?>
    <span><?=$page;?> / <?=$totalPages;?></span>
    <?php if ($page < $totalPages): ?>
      <a href="<?=htmlspecialchars(self_url(true,$page+1));?>">Next &rarr;</a>
    <?php endif;?>
  </div>
<?php endif;?>
</div>

<style>
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));color:var(--text-dark,#f5f5f5)
}
.badge{
    display:inline-block;min-width:48px;text-align:center;padding:.2rem .6rem;
    border-radius:9999px;background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.snap{width:100%;border-collapse:collapse;margin-top:1rem}
.snap th,.snap td{padding:.5rem .75rem;text-align:left}
.snap thead tr{background:rgba(255,255,255,.1);font-weight:600}
.snap tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
</style>
