<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // unified debug helper

/*───────────────────────────────────────────────────────────
 | helper: render_table (fallback if missing)
 *───────────────────────────────────────────────────────────*/
$helper = __DIR__ . '/../includes/table_helper.php';
if (is_readable($helper)) require_once $helper;
if (!function_exists('render_table')) {
    function render_table(array $headers, array $rows): string
    {
        $o = '<table class="ds-fb"><thead><tr>';
        foreach ($headers as $h) $o .= '<th>'.htmlspecialchars($h).'</th>';
        $o .= '</tr></thead><tbody>';
        if (!$rows) {
            $o .= '<tr><td colspan="'.count($headers).'">No data</td></tr>';
        } else {
            foreach ($rows as $r) {
                $o .= '<tr>';
                foreach ($headers as $key => $_) {
                    $o .= '<td>'.htmlspecialchars($r[$key] ?? '').'</td>';
                }
                $o .= '</tr>';
            }
        }
        return $o.'</tbody></table>';
    }
}

/*───────────────────────────────────────────────────────────
 | build API body
 *───────────────────────────────────────────────────────────*/
$customer = $_SESSION['selectedCustomer'] ?? null;
$dealer   = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';
$size     = 15;
$page     = max(1, (int)($_GET['ds_page'] ?? 1));

$body = [
    'PageNumber' => $page,
    'PageRows'   => $size,
    'SortColumn' => 'ExternalIdentifier',   // valid column
    'SortOrder'  => 'Asc',
] + ($customer ? ['CustomerCode'=>$customer] : ['FilterDealerId'=>$dealer]);

/*───────────────────────────────────────────────────────────
 | call wrapper
 *───────────────────────────────────────────────────────────*/
$api = (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['HTTP_HOST'].'/api/get_devices.php';
$ch  = curl_init($api);
curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_POSTFIELDS=>json_encode($body),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>10
]);
$raw = curl_exec($ch);
curl_close($ch);
$res = $raw ? json_decode($raw, true) : null;

$total = ($res['IsValid'] ?? false) ? ($res['TotalRows'] ?? 0) : 0;
$rows  = ($res['IsValid'] ?? false) ? ($res['Result']    ?? []) : [];

/*───────────────────────────────────────────────────────────
 | transform rows → ensure EquipmentID & Model populated
 *───────────────────────────────────────────────────────────*/
$normalized = [];
foreach ($rows as $r) {
    $normalized[] = [
        // choose first non-empty value in the chain
        'EquipmentID' => $r['ExternalIdentifier']
                      ?: ($r['AssetNumber']   ?? $r['SerialNumber'] ?? ''),
        'Model'       => $r['Model']
                      ?? ($r['ModelName']     ?? $r['ProductModel'] ?? ''),
        'IpAddress'   => $r['IpAddress']   ?? '',
        'Department'  => $r['Department']  ?? '',
    ];
}

/*───────────────────────────────────────────────────────────
 | pagination helpers
 *───────────────────────────────────────────────────────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $size));
function link_self(bool $exp,int $p=1):string{
    $q=['view'=>'sandbox']+($exp?['ds_exp'=>1,'ds_page'=>$p]:[]);
    return '/index.php?'.http_build_query($q);
}

/* headers after normalisation */
$headers = [
    'EquipmentID' => 'Equipment ID',
    'Model'       => 'Model',
    'IpAddress'   => 'IP Address',
    'Department'  => 'Department',
];
?>
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?=htmlspecialchars(link_self(!$expanded));?>" style="text-decoration:none;color:inherit">
        Devices Online <span class="badge"><?=$total;?></span>
    </a>
  </h2>

<?php if($expanded): ?>
  <?=render_table($headers,$normalized);?>

  <div class="pagination" style="text-align:center;margin-top:1rem">
    <?php if($page>1):?>
      <a href="<?=htmlspecialchars(link_self(true,$page-1));?>">&larr; Prev</a>
    <?php endif;?>
    <span><?=$page;?> / <?=$totalPages;?></span>
    <?php if($page<$totalPages):?>
      <a href="<?=htmlspecialchars(link_self(true,$page+1));?>">Next &rarr;</a>
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
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
.ds-fb{width:100%;border-collapse:collapse;margin-top:1rem}
.ds-fb th,.ds-fb td{padding:.5rem .75rem;text-align:left}
.ds-fb thead tr{background:rgba(255,255,255,.1);font-weight:600}
.ds-fb tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
</style>
