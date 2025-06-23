<?php
declare(strict_types=1);

// ─── Debug helper ─────────────────────────────────────────────
require_once __DIR__ . '/../includes/debug.php';

// ─── API helper ──────────────────────────────────────────────
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// ─── 1) Determine current customer ───────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customerCode = $_SESSION['selectedCustomer'] ?? '';
if ($customerCode === '') {
    echo '<p class="error">No customer selected.</p>';
    return;
}

// ─── 2) Call GET /CustomerDashboard?code=… ───────────────────
try {
    $resp = call_api(
        $config,
        'GET',
        'CustomerDashboard',
        ['code' => $customerCode]
    );
} catch (\Throwable $e) {
    error_log("[card_customer_devices] API error: " . $e->getMessage());
    $resp = [];
}

// ─── 3) Safely extract devices ───────────────────────────────
$valid    = is_array($resp) && !empty($resp['IsValid']);
$devices  = $valid && isset($resp['Result']['Devices'])
          ? $resp['Result']['Devices']
          : [];

// ─── 4) Normalize rows ──────────────────────────────────────
$rows = [];
foreach ($devices as $d) {
    $asset = trim((string)($d['AssetNumber']        ?? ''));
    $ext   = trim((string)($d['ExternalIdentifier'] ?? ''));
    $id    = $asset !== '' ? $asset : $ext;

    // some fields can be arrays; leave them as-is
    $dept = $d['Department'] ?? '';
    $note = $d['Notes']      ?? $d['Note'] ?? '';

    $rows[] = [
        'Identifier' => $id,
        'Department' => $dept,
        'Note'       => $note,
    ];
}

// ─── 5) Cell formatter ───────────────────────────────────────
function cell_html($v): string
{
    if (is_array($v)) {
        $html = '<table class="nested"><thead><tr><th>Key</th><th>Value</th></tr></thead><tbody>';
        foreach ($v as $k => $val) {
            $scalar = is_scalar($val) ? htmlspecialchars((string)$val) : htmlspecialchars(json_encode($val));
            $html .= '<tr><td>'.htmlspecialchars((string)$k).'</td><td>'.$scalar.'</td></tr>';
        }
        return $html . '</tbody></table>';
    }
    return htmlspecialchars((string)$v);
}

// ─── 6) Render the card ──────────────────────────────────────
$total = count($rows);
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
          <td><?= cell_html($r['Identifier']); ?></td>
          <td><?= cell_html($r['Department']); ?></td>
          <td><?= cell_html($r['Note']); ?></td>
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
    vertical-align:top;
}
.snap thead tr {
    background:rgba(255,255,255,.1);
    font-weight:600;
}
.snap tbody tr:nth-child(even) {
    background:rgba(255,255,255,.05);
}
/* nested array table */
.nested {
    width:100%;
    border-collapse:collapse;
    margin:0.25rem 0;
    font-size:0.8rem;
}
.nested th, .nested td {
    padding:0.2rem 0.4rem;
    border:1px solid rgba(255,255,255,0.2);
    text-align:left;
}
.nested thead th {
    background:rgba(255,255,255,0.1);
    font-weight:600;
}
</style>
