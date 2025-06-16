<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
$apiUrl = APP_BASE_URL . "api/get_device_alerts.php?customer=" . urlencode($customerCode);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

$alerts = $data['Result'] ?? [];

?>

<div class="device-card"
     data-card-id="supply_alerts_card"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <div class="card-header compact-header">
    <h3>Device Alerts (<?= count($alerts) ?>)</h3>
  </div>

  <?php if (empty($alerts)): ?>
    <p>No active supply alerts found for this customer.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table">
        <thead>
          <tr>
            <th>Equipment ID</th>
            <th>Department</th>
            <th>Warning</th>
            <th>Suggested Consumable</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alerts as $alert): ?>
            <tr>
              <td><?= htmlspecialchars($alert['ExternalIdentifier'] ?? '-') ?></td>
              <td><?= htmlspecialchars($alert['Department'] ?? '-') ?></td>
              <td><?= htmlspecialchars($alert['Warning'] ?? '-') ?></td>
              <td><?= htmlspecialchars($alert['SuggestedConsumable'] ?? '-') ?></td>
              <td>
                <button class="drilldown-btn" data-device-id="<?= htmlspecialchars($alert['DeviceId'] ?? '') ?>" title="View Details">
                  <span class="icon">🔍</span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<style>
.device-table th,
.device-table td {
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.device-table th {
  background: rgba(255,255,255,0.05);
}

.drilldown-btn {
  background: none;
  border: none;
  padding: 0;
  margin-left: 0.4rem;
  cursor: pointer;
  vertical-align: middle;
}
.drilldown-btn .icon {
  font-size: 0.75rem;
  line-height: 1;
  display: inline-block;
  transform: translateY(1px);
}
</style>