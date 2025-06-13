<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$deviceId = $_GET['id'] ?? null;
$customerCode = $_GET['customer'] ?? null;
$dashboardName = $_GET['dashboard'] ?? 'default';

if (!$deviceId || !$customerCode) {
    echo "<div class='device-card error'>Missing device ID or customer code.</div>";
    return;
}

$apiUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!isset($data['Result']) || !is_array($data['Result'])) {
    echo "<div class='device-card error'>Unable to retrieve device list.</div>";
    return;
}

$device = null;
foreach ($data['Result'] as $d) {
    if ((string)($d['Id'] ?? '') === (string)$deviceId) {
        $device = $d;
        break;
    }
}

if (!$device) {
    echo "<div class='device-card error'>Device not found.</div>";
    return;
}
?>

<div class="device-card"
     data-card-id="printer_card"
     data-dashboard="<?= htmlspecialchars($dashboardName) ?>"
     data-device-id="<?= htmlspecialchars($device['Id'] ?? '') ?>"
     data-customer-code="<?= htmlspecialchars($device['CustomerCode'] ?? '') ?>"
     data-serial="<?= htmlspecialchars($device['SerialNumber'] ?? '') ?>"
     data-status="<?= htmlspecialchars($device['Status'] ?? 'unknown') ?>"
     data-model="<?= htmlspecialchars($device['Model'] ?? '') ?>">

  <header class="card-header">
    <h3><?= htmlspecialchars($device['Model'] ?? 'Unknown Model') ?></h3>
    <span class="status-dot <?= ($device['Status'] ?? '') === 'OK' ? 'online' : 'offline' ?>"></span>
  </header>

  <ul class="device-info">
    <li><strong>Serial:</strong> <?= htmlspecialchars($device['SerialNumber'] ?? 'N/A') ?></li>
    <li><strong>Asset #:</strong> <?= htmlspecialchars($device['Code'] ?? 'N/A') ?></li>
    <li><strong>IP:</strong> <?= htmlspecialchars($device['IPAddress'] ?? 'N/A') ?></li>
    <li><strong>Customer:</strong> <?= htmlspecialchars($device['CustomerCode'] ?? 'N/A') ?></li>
    <li><strong>Status:</strong> <?= htmlspecialchars($device['Status'] ?? 'Unknown') ?></li>
  </ul>
</div>
