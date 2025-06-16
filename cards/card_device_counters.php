<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

$customer = $_GET['customer'] ?? 'W9OPXL0YDK';
$apiUrl = "/api/get_device_counters.php?customer=" . urlencode($customer);
$host = $_SERVER['HTTP_HOST'];
$scheme = $_SERVER['REQUEST_SCHEME'] ?? 'https';
$fullUrl = "$scheme://$host$apiUrl";

$data = json_decode(file_get_contents($fullUrl), true);

// Fix: Correct key is 'Result', not 'Data'
if (!isset($data['Result']) || !is_array($data['Result'])) {
  echo "<div class='card'><h2 class='card-title'>Device Counters</h2><p>No counter data available.</p></div>";
  return;
}
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
        <?php foreach ($data['Result'] as $device): ?>
          <?php
            $id         = $device['DeviceId'] ?? '';
            $external   = $device['ExternalIdentifier'] ?? 'N/A';
            $dept       = $device['OfficeDescription'] ?? '';
            $rawDetails = $device['CountersDetailed'] ?? [];

            $total = 0;
            foreach ($rawDetails as $entry) {
              $total += $entry['Total'] ?? 0;
            }

            $tooltip = htmlspecialchars(json_encode($rawDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
          ?>
          <tr class="hover-row" data-device-id="<?= htmlspecialchars($id) ?>">
            <td><span class="drill-icon" title="Details" onclick="openDrilldown('<?= htmlspecialchars($id) ?>')">🔍</span></td>
            <td title="<?= $tooltip ?>"><?= htmlspecialchars($external) ?></td>
            <td><?= htmlspecialchars($dept) ?></td>
            <td><?= number_format($total) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
