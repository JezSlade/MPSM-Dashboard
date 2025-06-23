<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // central debug

/* ── 1. make sure render_table exists ───────────────────────── */
$helper = __DIR__ . '/../includes/table_helper.php';
if (is_readable($helper)) require_once $helper;
if (!function_exists('render_table')) {
    function render_table(array $head, array $rows): string
    {
        $t = '<table class="ds"><thead><tr>';
        foreach ($head as $h) $t .= '<th>'.htmlspecialchars($h).'</th>';
        $t .= '</tr></thead><tbody>';
        if (!$rows) {
            $t .= '<tr><td colspan="'.count($head).'">No data</td></tr>';
        } else {
            foreach ($rows as $r) {
                $t .= '<tr>';
                foreach ($head as $key => $_) {
                    $t .= '<td>'.htmlspecialchars($r[$key] ?? '').'</td>';
                }
                $t .= '</tr>';
            }
        }
        return $t.'</tbody></table>';
    }
}

/* ── 2. build request to wrapper ────────────────────────────── */
$customer = $_SESSION['selectedCustomer'] ?? null;
$dealer   = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';
$perPage  = 15;
$page     = max(1, (int)($_GET['ds_page'] ?? 1));

$body = [
    'PageNumber' => $page,
    'PageRows'   => $perPage,
    'SortColumn' => 'ExternalIdentifier',   // valid
    'SortOrder'  => 'Asc',
] + ($customer ? ['CustomerCode'=>$customer] : ['FilterDealerId'=>$dealer]);

$api = (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['HTTP_HOST'].'/api/get_devices.php';
$ch  = curl_init($api);
curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_POSTFIELDS=>json_encode($body),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>10
]);
$raw = curl_exec($ch); curl_close($ch);
$resp = $raw ? json_decode($raw, true) : null;

$total   = ($resp['IsValid'] ?? false) ? ($resp['TotalRows'] ?? 0) : 0;
$rawRows = ($resp['IsValid'] ?? false) ? ($resp['Result']    ?? []) : [];

/* ── 3. dynamic field discovery ─────────────────────────────── */
$map = [
    // candidate lists in priority order
    'EquipmentID' => ['ExternalIdentifier','AssetNumber','SerialNumber','DeviceId','Id'],
    'Model'       => ['Model','ModelName','ProductModel','Description'],
    'IpAddress'   => ['IpAddress','IPAddress','IP'],
    'Department'  => ['Department','Dept'],
];

$rows = [];
foreach ($rawRows as $r) {
    $rows[] = [
        'EquipmentID'=> first_value($r, $map['EquipmentID']),
        'Model'      => first_value($r, $map['Model']),
        'IpAddress'  => first_value($r, $map['IpAddress']),
        'Department' => first_value($r, $map['Department']),
    ];
}

/* helper to pick first non-empty value */
function first_value(array $row, array $keys): string
{
    foreach ($keys as $k) {
        if (!empty($row[$k])) return (string)$row[$k];
    }
    return '';
}

/* ── 4. pagination helpers ──────────────────────────────────── */
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $perPage));
function mkurl(bool $exp,int $p=1):string{
    $q=['view'=>'sandbox']+($exp?['ds_exp'=>1,'ds_page'=>$p]:[]);
    return '/index.php?'.http_build_query($q);
}

/* ── 5. table headers (keys must match $rows) ───────────────── */
$headers = [
    'EquipmentID' => 'Equipment ID',
    'Model'       => 'Model',
    'IpAddress'   => 'IP Address',
    'Department'  => 'Department',
];
?>
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?=htmlspecialchars(mkurl(!$expanded));?>" style="color:inherit;text-decoration:none">
        Devices Online <span class="badge"><?=$total;?></span>
    </a>
  </h2>

<?php if($expanded): ?>
  <?= render_table($headers,$rows); ?>
  <div class="pagination" style="text-align:center;margin-top:1rem">
    <?php if($page>1):?>
      <a href="<?=htmlspecialchars(mkurl(true,$page-1));?>">&larr; Prev</a>
    <?php endif;?>
    <span><?=$page;?> / <?=$totalPages;?></span>
    <?php if($page<$totalPages):?>
      <a href="<?=htmlspecialchars(mkurl(true,$page+1));?>">Next &rarr;</a>
    <?php endif;?>
  </div>
<?php endif;?>
</div>

<style>
.card.devices-snapshot{padding:1.5rem;border-radius:12px;backdrop-filter:blur(10px);
  background:var(--bg-card,rgba(255,255,255,.08));color:var(--text-dark,#f5f5f5)}
.badge{display:inline-block;min-width:48px;text-align:center;padding:.2rem .6rem;border-radius:9999px;
  background:var(--bg-light,#2d8cff);color:#fff;font-weight:600}
.ds th,.ds td,.ds-fb th,.ds-fb td{padding:.5rem .75rem;text-align:left}
.ds thead tr,.ds-fb thead tr{background:rgba(255,255,255,.1);font-weight:600}
.ds tbody tr:nth-child(even),.ds-fb tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
</style>
