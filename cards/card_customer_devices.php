<?php
declare(strict_types=1);

// 0) Debug & API helpers
require_once __DIR__ . '/../includes/debug.php';
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 1) Determine current customer
if (session_status() === PHP_SESSION_NONE) session_start();
$code = $_SESSION['selectedCustomer'] ?? '';
if ($code === '') {
    echo '<p class="error">No customer selected.</p>';
    return;
}

// 2) Call GET /CustomerDashboard?code=…
try {
    $resp = call_api(
        $config,
        'GET',
        'CustomerDashboard',
        ['code' => $code]
    );
} catch (\Throwable $e) {
    error_log("CustomerDashboard error: ".$e->getMessage());
    $resp = [];
}

// 3) Safely pull out the devices array
$devices = [];
if (!empty($resp['IsValid']) && isset($resp['Result']['Devices'])) {
    $devices = $resp['Result']['Devices'];
}
$total = is_array($devices) ? count($devices) : 0;

// 4) Normalize rows (merge AssetNumber/ExternalIdentifier)
$rows = [];
foreach ($devices as $d) {
    $asset = trim((string)($d['AssetNumber']        ?? ''));
    $ext   = trim((string)($d['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;
    $rows[] = [
        'Identifier' => $id,
        'Department' => $d['OfficeId']   ?? $d['Department'] ?? '',
        'Note'       => $d['Contact']    ?? $d['Notes']      ?? '',
    ];
}

// 5) Render
?>
<div class="card customer-devices">
  <header>
    <h2 style="margin:0;font-size:1.25rem;font-weight:700">
      Devices Online <span class="badge"><?= $total; ?></span>
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
  padding:1.2rem;border-radius:12px;backdrop-filter:blur(10px);
  background:var(--bg-card,rgba(255,255,255,.08));color:var(--text-dark,#f5f5f5);
  margin-bottom:1rem;
}
.badge {
  display:inline-block;min-width:44px;text-align:center;padding:.2rem .5rem;
  border-radius:9999px;background:var(--bg-light,#2d8cff);
  color:#fff;font-weight:600;font-size:0.85rem;
}
.snap {
  font-size:0.85rem;width:100%;border-collapse:collapse;margin-top:.75rem;
}
.snap th,.snap td {padding:.4rem .6rem;text-align:left;}
.snap thead tr {background:rgba(255,255,255,.1);font-weight:600;}
.snap tbody tr:nth-child(even){background:rgba(255,255,255,.05);}
</style>
