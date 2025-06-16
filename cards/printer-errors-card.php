<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
$apiUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!isset($data['Result']) || !is_array($data['Result'])) {
    echo "<div class='device-card error'>Unable to fetch device list.</div>";
    return;
}

$devices = array_filter($data['Result'], function($device) {
    return isset($device['Errors']) && is_array($device['Errors']) && count($device['Errors']) > 0;
});

usort($devices, function ($a, $b) {
    return strcmp($a['ExternalIdentifier'] ?? '', $b['ExternalIdentifier'] ?? '');
});

$columns = ['ExternalIdentifier', 'IpAddress', 'Model', 'Errors'];
?>

<div class="device-card"
     data-card-id="printer_errors_card"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <div class="card-header compact-header">
    <h3>Devices With Alerts (<?= count($devices) ?>)</h3>
  </div>

  <?php if (empty($devices)): ?>
    <p>No active alerts or device errors found for this customer.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table">
        <thead>
          <tr>
            <?php foreach ($columns as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($devices as $device): ?>
            <tr>
              <td><?= htmlspecialchars($device['ExternalIdentifier'] ?? '-') ?></td>
              <td><?= htmlspecialchars($device['IpAddress'] ?? '-') ?></td>
              <td><?= htmlspecialchars($device['Model'] ?? '-') ?></td>
              <td>
                <?php
                  if (!empty($device['Errors']) && is_array($device['Errors'])) {
                      foreach ($device['Errors'] as $err) {
                          echo "<div class='error-chip'>" . htmlspecialchars($err['Description'] ?? 'Unknown') . "</div>";
                      }
                  } else {
                      echo "-";
                  }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<style>
.device-card h3 {
  font-size: 1.1rem;
  margin: 0 0 0.5rem 0;
}

.error-chip {
  display: inline-block;
  background: rgba(255, 80, 80, 0.15);
  color: #ff8080;
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  margin: 0.1rem;
  border-radius: 0.3rem;
  border: 1px solid rgba(255, 80, 80, 0.3);
}

.device-table th,
.device-table td {
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.device-table th {
  background: rgba(255,255,255,0.05);
}
</style>