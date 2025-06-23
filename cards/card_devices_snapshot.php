<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';

/*──────────────────────────────
 | 0) SESSION & CUSTOMER
 *──────────────────────────────*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customer = $_SESSION['selectedCustomer'] ?? '';

/*──────────────────────────────
 | 1) BUILD & LOG REQUEST
 *──────────────────────────────*/
$body = ['Code' => $customer];
// If your API actually needs DealerCode too, uncomment next line:
// $body['DealerCode'] = getenv('DEALER_CODE') ?: '';

error_log('[cust_devices] Request: ' . json_encode($body));

$api = (isset($_SERVER['HTTPS'])?'https://':'http://')
     . $_SERVER['HTTP_HOST']
     . '/api/customer_dashboard_devices.php';

/*──────────────────────────────
 | 2) CALL API & LOG RESPONSE
 *──────────────────────────────*/
$ch = curl_init($api);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);
$raw = curl_exec($ch);
curl_close($ch);

error_log('[cust_devices] Raw response: ' . ($raw ?? 'NULL'));

$data    = $raw ? json_decode($raw, true) : null;
$total   = ($data['IsValid'] ?? false)
         ? ($data['Result']['TotalCount'] ?? 0)
         : 0;
$devices = ($data['IsValid'] ?? false)
         ? ($data['Result']['Devices']   ?? [])
         : [];

/*──────────────────────────────
 | 3) NORMALISE ROWS
 *──────────────────────────────*/
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

/*──────────────────────────────
 | 4) RENDER CARD
 *──────────────────────────────*/
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
      <?php endforeach; endif;?>
    </tbody>
  </table>
</div>

<style>
.card.customer-devices { /* ... your styles ... */ }
.badge { /* ... */ }
.snap { /* ... */ }
.snap th, .snap td { /* ... */ }
.snap thead tr { /* ... */ }
.snap tbody tr:nth-child(even) { /* ... */ }
</style>
