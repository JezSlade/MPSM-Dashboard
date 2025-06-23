<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';

/*────────── 0) SESSION & FILTER ──────────*/
if (session_status() === PHP_SESSION_NONE) session_start();
$customer = $_SESSION['selectedCustomer'] ?? '';
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

/*────────── 1) BUILD LIST REQUEST ────────*/
$pageSize = 15;
$page     = max(1, (int)($_GET['ds_page'] ?? 1));

$body = [
    'PageNumber'        => $page,
    'PageRows'          => $pageSize,
    'SortColumn'        => 'ExternalIdentifier',
    'SortOrder'         => 'Desc',
    'FilterDealerId'    => $dealerId,
    'DeviceType'        => 'Printer',
];
if ($customer !== '') {
    $body['FilterCustomerCodes'] = [$customer];
}
$apiList = (isset($_SERVER['HTTPS'])?'https://':'http://')
         . $_SERVER['HTTP_HOST'] . '/api/get_devices.php';
$ch = curl_init($apiList);
curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_POSTFIELDS=>json_encode($body),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>10,
]);
$listRaw = curl_exec($ch); curl_close($ch);
$listData = $listRaw ? json_decode($listRaw, true) : null;

$total = ($listData['IsValid'] ?? false) ? ($listData['TotalRows'] ?? 0) : 0;
$raw   = ($listData['IsValid'] ?? false) ? ($listData['Result']    ?? []) : [];

/*────────── 2) NORMALISE ROWS ────────────*/
$rows = [];
foreach ($raw as $r) {
    $asset = trim($r['AssetNumber'] ?? '');
    $ext   = trim($r['ExternalIdentifier'] ?? '');
    $id    = $asset !== '' ? $asset : $ext;
    $rows[] = [
        'Drill'      => $r['DeviceId'] ?? $r['Id'] ?? '',
        'Identifier' => $id,
        'Department' => $r['Department'] ?? '',
        'Note'       => $r['Note'] ?? $r['Notes'] ?? '',
    ];
}

/*────────── 3) PAGINATION HELPERS ────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total/$pageSize));
function self_url(bool $e,int $p=1){ 
    $q=['view'=>'dashboard'] + ($e?['ds_exp'=>1,'ds_page'=>$p]:[]);
    return '/index.php?'.http_build_query($q);
}
?>
<!-- ─────── CARD MARKUP ─────── -->
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?=htmlspecialchars(self_url(!$expanded));?>" style="text-decoration:none;color:inherit">
      Devices Online <span class="badge"><?=$total;?></span>
    </a>
  </h2>

  <?php if($expanded): ?>
    <table class="snap">
      <thead>
        <tr>
          <th></th><th>Asset&nbsp;/&nbsp;Ext&nbsp;ID</th><th>Department</th><th>Note</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="4">No data</td></tr>
        <?php else: foreach($rows as $r):
          $deviceId = htmlspecialchars($r['Drill']);
        ?>
          <tr>
            <td>
              <button class="drill-btn" data-id="<?=$deviceId;?>" title="View all details">
                <!-- Feather “info” icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" class="feather feather-info">
                  <circle cx="8" cy="8" r="7"></circle>
                  <line x1="8" y1="11" x2="8" y2="8"></line>
                  <circle cx="8" cy="5" r="1"></circle>
                </svg>
              </button>
            </td>
            <td><?=htmlspecialchars($r['Identifier']);?></td>
            <td><?=htmlspecialchars($r['Department']);?></td>
            <td><?=htmlspecialchars($r['Note']);?></td>
          </tr>
        <?php endforeach; endif;?>
      </tbody>
    </table>

    <div class="pagination" style="text-align:center;margin-top:1rem">
      <?php if($page>1):?><a href="<?=htmlspecialchars(self_url(true,$page-1));?>">&larr; Prev</a><?php endif;?>
      <span><?=$page;?> / <?=$totalPages;?></span>
      <?php if($page<$totalPages):?><a href="<?=htmlspecialchars(self_url(true,$page+1));?>">Next &rarr;</a><?php endif;?>
    </div>
  <?php endif;?>
</div>

<!-- ─────── MODAL OVERLAY ─────── -->
<div id="device-modal" class="modal-overlay" hidden>
  <div class="modal-content">
    <button id="modal-close" class="modal-close" title="Close">×</button>
    <pre id="modal-body">Loading…</pre>
  </div>
</div>

<style>
/* reuse your compact table styles… */
.snap{font-size:0.85rem;width:100%;border-collapse:collapse;margin-top:1rem}
.snap th,.snap td{padding:.4rem .6rem;text-align:left}
.snap thead tr{background:rgba(255,255,255,.1);font-weight:600}
.snap tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
/* Modal styles */
.modal-overlay{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;
  z-index:1000;
}
.modal-content{
  background:var(--bg-card,rgba(255,255,255,0.1));
  color:var(--text-dark,#f5f5f5);
  padding:1rem;border-radius:8px;max-width:80%;max-height:80%;overflow:auto;
  position:relative;font-family:monospace;font-size:0.85rem;
}
.modal-close{
  position:absolute;top:0.5rem;right:0.75rem;
  background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer;
}
</style>

<script>
// Fetch and display device details in the modal
document.querySelectorAll('.drill-btn').forEach(btn=>{
  btn.addEventListener('click',async ()=>{
    const id = btn.dataset.id;
    const modal = document.getElementById('device-modal');
    const body  = document.getElementById('modal-body');
    modal.hidden = false; body.textContent = 'Loading…';

    try {
      const resp = await fetch('/api/get_device_details.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ DeviceId: id })
      });
      const json = await resp.json();
      if(json.IsValid && json.Result){
        body.textContent = JSON.stringify(json.Result, null, 2);
      } else {
        body.textContent = 'Error: ' + JSON.stringify(json.Errors || json);
      }
    } catch(e){
      body.textContent = 'Network error: ' + e;
    }
  });
});

// Close modal
document.getElementById('modal-close').addEventListener('click',()=>{
  document.getElementById('device-modal').hidden = true;
});
</script>
