<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? null;
$devices = [];

if ($customerCode) {
    $apiUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
    try {
        $response = file_get_contents($apiUrl);
        $json = json_decode($response, true);
        if (isset($json['Result']) && is_array($json['Result'])) {
            $devices = $json['Result'];
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error loading devices for customer: " . htmlspecialchars($customerCode) . "</p>";
    }
}
?>

<section>
  <?php if (!$customerCode): ?>
    <p>Please select a customer from the menu to view their devices.</p>
  <?php elseif (empty($devices)): ?>
    <p>No devices found for customer <strong><?= htmlspecialchars($customerCode) ?></strong>.</p>
  <?php else: ?>
    <div class="device-grid">
      <?php foreach ($devices as $device): ?>
        <?php
          $_GET['id'] = $device['Id'] ?? '';
          $_GET['customer'] = $device['CustomerCode'] ?? '';
          include __DIR__ . '/../cards/printer_card.php';
        ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
