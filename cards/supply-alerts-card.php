<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
$pageRows = 15;

// Step 1: Get device alerts
$alertsUrl = APP_BASE_URL . "api/get_device_alerts.php?customer=" . urlencode($customerCode);
$alertsJson = @file_get_contents($alertsUrl);
$alertsData = json_decode($alertsJson, true);
$alerts = $alertsData['Result'] ?? [];

// Step 2: Get devices for Equipment ID mapping
$devicesUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
$devicesJson = @file_get_contents($devicesUrl);
$deviceData = json_decode($devicesJson, true);
$deviceList = $deviceData['Result'] ?? [];

$deviceMap = [];
foreach ($deviceList as $dev) {
    if (!empty($dev['Id']) && !empty($dev['Equipment ID'])) {
        $deviceMap[$dev['Id']] = $dev['Equipment ID'];
    }
}

// Step 3: Consolidate alerts by DeviceId
$grouped = [];
foreach ($alerts as $alert) {
    $id = $alert['DeviceId'];
    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'DeviceId' => $id,
            'Equipment ID' => $deviceMap[$id] ?? '—',
            'Department' => $alert['Department'] ?? '-',
            'Warnings' => [],
            'Consumables' => [],
        ];
    }
    if (!empty($alert['Warning'])) {
        $grouped[$id]['Warnings'][] = $alert['Warning'];
    }
    if (!empty($alert['SuggestedConsumable'])) {
        $grouped[$id]['Consumables'][] = $alert['SuggestedConsumable'];
    }
}

// Apply pagination after grouping
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalDevices = count($grouped);
$totalPages = ceil($totalDevices / $pageRows);
$offset = ($currentPage - 1) * $pageRows;
$grouped = array_slice($grouped, $offset, $pageRows);
?>

<div class="device-card"
     data-card-id="supply_alerts_card"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <div class="card-header compact-header">
    <h3>Device Alerts (<?= count($grouped) ?>)</h3>
  </div>

  <?php if (empty($grouped)): ?>
    <p>No active supply alerts found for this customer.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table">
        <thead>
          <tr>
            <th></th>
            <th>Equipment ID</th>
            <th>Department</th>
            <th>Warning</th>
            <th>Suggested Consumable</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($grouped as $row): ?>
            <tr>
              <td>
                <button class="drilldown-btn" data-device-id="<?= htmlspecialchars($row['DeviceId']) ?>" title="View Details">
                  🔍
                </button>
              </td>
              <td><?= htmlspecialchars($row['Equipment ID']) ?></td>
              <td><?= htmlspecialchars($row['Department']) ?></td>
              <td><?= htmlspecialchars(implode(', ', array_unique($row['Warnings']))) ?></td>
              <td><?= htmlspecialchars(implode(', ', array_unique($row['Consumables']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></table>

<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage - 1 ?>" class="page-btn">← Prev</a>
  <?php endif; ?>

  <span class="page-label">Page <?= $currentPage ?> of <?= $totalPages ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage + 1 ?>" class="page-btn">Next →</a>
  <?php endif; ?>
</div>

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
