<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';

/* 0) SESSION & CUSTOMER */
if (session_status() === PHP_SESSION_NONE) session_start();
$customer = $_SESSION['selectedCustomer'] ?? '';
if ($customer === '') {
    echo '<p class="error">No customer selected.</p>';
    return;
}

/* 1) FETCH via GET /api/customer_dashboard.php?code=… */
$api = (isset($_SERVER['HTTPS'])?'https://':'http://')
     . $_SERVER['HTTP_HOST']
     . '/api/customer_dashboard.php?code='
     . urlencode($customer);

error_log("[snapshot] Fetching $api");
$ch   = curl_init($api);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);
$raw  = curl_exec($ch);
curl_close($ch);
error_log('[snapshot] Response: '.($raw??'NULL'));

$data    = $raw ? json_decode($raw, true) : [];
$valid   = !empty($data['IsValid']);
$devices = $valid && isset($data['Result']['Devices'])
         ? $data['Result']['Devices'] : [];
$total   = is_array($devices) ? count($devices) : 0;

/* 2) NORMALISE ROWS */
$rows = [];
foreach ($devices as $d) {
    $asset = trim((string)($d['AssetNumber'] ?? ''));
    $ext   = trim((string)($d['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;
    $rows[] = [
        'Identifier' => $id,
        'Department' => $d['Department'] ?? $d['OfficeId'] ?? '',
        'Note'       => $d['Notes']      ?? $d['Note'] ?? '',
    ];
}

/* 3) RENDER CARD */
?>
<div class="card customer-devices">
  <header>
    <h2 style="margin:0;font-size:1.25rem;font-weight:700">
      Device Snapshot <span class="badge"><?= $total; ?></span>
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
          <td><?= htmlspecialchars($r['Identifier']); ?></td>
          <td><?= htmlspecialchars($r['Department']); ?></td>
          <td><?= htmlspecialchars($r['Note']); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<style>
.card.customer-devices {
    padding:1.2rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));
    color:var(--text-dark,#f5f5f5);
    margin-bottom:1rem;
}
.badge {
    display:inline-block;min-width:44px;text-align:center;
    padding:.2rem .5rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600;
    font-size:0.85rem;
}
.snap {
    font-size:0.85rem;width:100%;border-collapse:collapse;margin-top:.75rem;
}
.snap th, .snap td {
    padding:.4rem .6rem;text-align:left;
}
.snap thead tr {
    background:rgba(255,255,255,.1);font-weight:600;
}
.snap tbody tr:nth-child(even) {
    background:rgba(255,255,255,.05);
}
</style>
