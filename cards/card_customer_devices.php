<?php
declare(strict_types=1);

// Always-on debug
require_once __DIR__ . '/../includes/debug.php';

// Shared API functions
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

/*────────────────────────────────────
│ 1) Determine which customer
└────────────────────────────────────*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customerCode = $_SESSION['selectedCustomer'] ?? '';
if ($customerCode === '') {
    // nothing to do
    echo '<p class="error">No customer selected.</p>';
    return;
}

/*────────────────────────────────────
│ 2) Call the “CustomerDashboard/Devices” endpoint
│    Expects body { request: { Code: "…" } }
└────────────────────────────────────*/
try {
    $resp = call_api(
        $config,
        'POST',
        'CustomerDashboard/Devices',
        ['request' => ['Code' => $customerCode]]
    );
} catch (\Throwable $e) {
    error_log("[card_customer_devices] API error: " . $e->getMessage());
    $resp = ['IsValid' => false];
}

$total   = $resp['IsValid'] 
         ? ($resp['Result']['TotalCount']   ?? 0) 
         : 0;
$devices = $resp['IsValid'] 
         ? ($resp['Result']['Devices']      ?? []) 
         : [];

/*────────────────────────────────────
│ 3) Normalize into rows
└────────────────────────────────────*/
$rows = [];
foreach ($devices as $d) {
    $asset = trim((string)($d['AssetNumber']        ?? ''));
    $ext   = trim((string)($d['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;

    $rows[] = [
        'Identifier' => $id,
        'Department' => $d['Department'] ?? '',
        'Note'       => $d['Note']       ?? $d['Notes'] ?? '',
    ];
}

/*────────────────────────────────────
│ 4) Render the card
└────────────────────────────────────*/
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
    padding:1.2rem;
    border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));
    color:var(--text-dark,#f5f5f5);
    margin-bottom:1rem;
}
.badge {
    display:inline-block;
    min-width:44px;
    text-align:center;
    padding:.2rem .5rem;
    border-radius:9999px;
    background:var(--bg-light,#2d8cff);
    color:#fff;
    font-weight:600;
    font-size:0.85rem;
}
.snap {
    font-size:0.85rem;
    width:100%;
    border-collapse:collapse;
    margin-top:.75rem;
}
.snap th, .snap td {
    padding:.4rem .6rem;
    text-align:left;
}
.snap thead tr {
    background:rgba(255,255,255,.1);
    font-weight:600;
}
.snap tbody tr:nth-child(even) {
    background:rgba(255,255,255,.05);
}
</style>
