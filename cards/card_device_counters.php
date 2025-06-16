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

// Fail-safe
if (!isset($data['Data']) || !is_array($data['Data'])) {
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
          <th>Mono</th>
          <th>Color</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['Data'] as $device): ?>
          <?php
            $id       = $device['DeviceId'] ?? '';
            $external = $device['ExternalIdentifier'] ?? 'N/A';
            $dept     = $device['OfficeDescription'] ?? '';
            $mono     = 0;
            $color    = 0;

            foreach ($device['Counters'] ?? [] as $counter) {
              $mono  += $counter['Mono'] ?? 0;
              $color += $counter['Color'] ?? 0;
            }
          ?>
          <tr class="hover-row" data-device-id="<?= htmlspecialchars($id) ?>">
            <td><span class="drill-icon" title="View Details" onclick="openDrilldown('<?= htmlspecialchars($id) ?>')">🔍</span></td>
            <td><?= htmlspecialchars($external) ?></td>
            <td><?= htmlspecialchars($dept) ?></td>
            <td><?= number_format($mono) ?></td>
            <td><?= number_format($color) ?></td>
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

<!-- Use correct stylesheet path -->
<link rel="stylesheet" href="/public/css/styles.css">
