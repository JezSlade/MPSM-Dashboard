<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/api_client.php';

// 1) Load env & session
$env = load_env(__DIR__ . '/../.env');
if (session_status() === PHP_SESSION_NONE) session_start();
$code = $_SESSION['selectedCustomer'] ?? '';
if ($code === '') {
    echo '<p class="error">No customer selected.</p>';
    return;
}

// 2) Fetch devices
$resp = api_call(
    $env,
    'POST',
    '/CustomerDashboard/Devices',
    ['request'=>[
        'Code'=>$code,
        'PageNumber'=>$_GET['page']    ?? 1,
        'PageRows'  =>$_GET['perPage'] ?? 15,
        'SortColumn'=>'ExternalIdentifier',
        'SortOrder'=>'Asc'
    ]]
);

// 3) Extract
$total   = $resp['IsValid'] ? (int)($resp['Result']['TotalCount'] ?? 0) : 0;
$devices = $resp['IsValid'] ? ($resp['Result']['Devices'] ?? []) : [];

// 4) Normalize & render (same as before)…
?>
<div class="card customer-devices">
  <header>
    <h2>Devices Online <span class="badge"><?= $total ?></span></h2>
  </header>
  <table class="snap">
    <thead><tr>
      <th>Asset/Ext ID</th><th>Department</th><th>Note</th>
    </tr></thead>
    <tbody>
      <?php if (!$devices): ?>
        <tr><td colspan="3">No data</td></tr>
      <?php else: foreach ($devices as $d):
        $id = $d['AssetNumber'] ?: $d['ExternalIdentifier'] ?? '';
      ?>
        <tr>
          <td><?=htmlspecialchars($id)?></td>
          <td><?=htmlspecialchars($d['Department']??'')?></td>
          <td><?=htmlspecialchars($d['Note']??$d['Notes']??'')?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  <!-- pagination as before -->
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
    font-size:0.85rem; width:100%; border-collapse:collapse; margin-top:.75rem;
}
.snap th, .snap td { padding:.4rem .6rem; text-align:left; }
.snap thead tr { background:rgba(255,255,255,.1); font-weight:600; }
.snap tbody tr:nth-child(even) { background:rgba(255,255,255,.05); }

.pagination {
    margin-top:1rem; text-align:center;
}
.pagination a {
    display:inline-block;
    margin:0 .25rem;
    padding:.25rem .5rem;
    border-radius:4px;
    background:rgba(255,255,255,.1);
    text-decoration:none;
    color:inherit;
}
.pagination a.active {
    background:rgba(255,255,255,.2);
    font-weight:600;
}
</style>
