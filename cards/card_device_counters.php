<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

$customer = $_GET['customer'] ?? 'W9OPXL0YDK';
$page     = (int)($_GET['page'] ?? 1);
$perPage  = 15;

// === Load Device Counters ===
$host    = $_SERVER['HTTP_HOST'];
$scheme  = $_SERVER['REQUEST_SCHEME'] ?? 'https';
$countersUrl = "$scheme://$host/api/get_device_counters.php?customer=" . urlencode($customer);
$counters = json_decode(file_get_contents($countersUrl), true);
$results = $counters['Result'] ?? [];

// === Load Device Lookup (for ExternalIdentifier) ===
$deviceUrl = "$scheme://$host/api/get_devices.php?customer=" . urlencode($customer);
$deviceList = json_decode(file_get_contents($deviceUrl), true);
$deviceLookup = [];

foreach ($deviceList['Result'] ?? [] as $dev) {
  $deviceLookup[$dev['Id']] = $dev['ExternalIdentifier'] ?? 'N/A';
}

// === Pagination Logic ===
$totalItems = count($results);
$totalPages = ceil($totalItems / $perPage);
$currentPage = max(1, min($page, $totalPages));
$start = ($currentPage - 1) * $perPage;
$visible = array_slice($results, $start, $perPage);
?>

<div class="card">
  <h2 class="card-title">Device Counters</h2>

  <div class="table-container">
    <table class="data-table full-width">
      <thead>
        <tr>
          <th></th>
          <th>External ID</th>
          <th>Department</th>
          <th>Total Count</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($visible as $device): ?>
          <?php
            $id     = $device['DeviceId'] ?? '';
            $dept   = $device['OfficeDescription'] ?? '';
            $external = $deviceLookup[$id] ?? 'N/A';

            $total = 0;
            foreach ($device['CountersDetailed'] ?? [] as $c) {
              $total += $c['Total'] ?? 0;
            }

            // Tooltip-style preview
            $tooltip = '';
            foreach ($device['CountersDetailed'] ?? [] as $entry) {
              if (!empty($entry['Description']) && isset($entry['Total'])) {
                $tooltip .= htmlspecialchars($entry['Description']) . ': ' . number_format($entry['Total']) . "\n";
              }
            }
          ?>
          <tr class="hover-row" title="<?= trim($tooltip) ?>">
            <td><span class="drill-icon" onclick="openDrilldown('<?= htmlspecialchars($id) ?>')">🔍</span></td>
            <td><?= htmlspecialchars($external) ?></td>
            <td><?= htmlspecialchars($dept) ?></td>
            <td><?= number_format($total) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?view=device_counters&customer=<?= urlencode($customer) ?>&page=<?= $i ?>"
         class="page-link<?= ($i === $currentPage) ? ' active' : '' ?>">
         <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

<script>
function openDrilldown(id) {
  fetch(`/components/drilldown-modal.php?id=${id}`)
    .then(r => r.text())
    .then(html => {
      const modal = document.createElement('div');
      modal.innerHTML = html;
      document.body.appendChild(modal);
    });
}
</script>

<link rel="stylesheet" href="/public/css/styles.css">
