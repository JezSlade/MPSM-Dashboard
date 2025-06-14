<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? null;
$currentPage = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

if (!$customerCode) {
    echo "<div class='device-card error'>No customer selected.</div>";
    return;
}

$apiUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!isset($data['Result']) || !is_array($data['Result'])) {
    echo "<div class='device-card error'>Unable to fetch device list.</div>";
    return;
}

$devices = $data['Result'];
$totalDevices = count($devices);
$totalPages = ceil($totalDevices / $perPage);
$offset = ($currentPage - 1) * $perPage;
$paginatedDevices = array_slice($devices, $offset, $perPage);

// collect all unique keys
$allKeys = [];
foreach ($devices as $device) {
    $allKeys = array_unique(array_merge($allKeys, array_keys($device)));
}
sort($allKeys);
?>

<div class="device-card"
     data-card-id="printer_card"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <header class="card-header">
    <h3>All Device Data for: <?= htmlspecialchars($customerCode) ?></h3>
  </header>

  <?php if (empty($paginatedDevices)): ?>
    <p>No devices found for this page.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table">
        <thead>
          <tr>
            <?php foreach ($allKeys as $key): ?>
              <th><?= htmlspecialchars($key) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginatedDevices as $device): ?>
            <tr>
              <?php foreach ($allKeys as $key): ?>
                <td>
                  <?php
                    $value = $device[$key] ?? '';
                    echo is_array($value)
                        ? '[array]'
                        : htmlspecialchars((string)$value);
                  ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination-nav">
      <?php if ($currentPage > 1): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage - 1 ?>" class="page-link">← Prev</a>
      <?php endif; ?>
      <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
      <?php if ($currentPage < $totalPages): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage + 1 ?>" class="page-link">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
