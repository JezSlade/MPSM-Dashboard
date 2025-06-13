<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/config.php';

// Load customers list via internal API
$customers = [];
try {
    $apiUrl = APP_BASE_URL . 'api/get_customers.php';
    $response = file_get_contents($apiUrl);
    $json = json_decode($response, true);
    if (isset($json['Result']) && is_array($json['Result'])) {
        $customers = $json['Result'];
    }
} catch (Exception $e) {
    // Fail silently, keep customers empty
}

$selected_customer = $_GET['customer'] ?? '';
?>
<nav class="glass-nav" style="display: flex; justify-content: space-between; align-items: center;">
  <ul style="display: flex; gap: 1rem; list-style: none; margin: 0; padding: 0;">
    <li><a href="<?= APP_BASE_URL ?>">Home</a></li>
    <!-- add more links here -->
  </ul>
  <form method="GET" action="<?= APP_BASE_URL ?>" style="display: flex; align-items: center;">
    <label for="customer" style="margin-right: 0.5rem; font-weight: 500;">Customer:</label>
    <select name="customer" id="customer" onchange="this.form.submit()" class="customer-select">
      <option value="">-- All Customers --</option>
      <?php foreach ($customers as $cust): ?>
<option value="<?= htmlspecialchars($cust['Code']) ?>" <?= $selected_customer === $cust['Code'] ? 'selected' : '' ?>>
  <?= htmlspecialchars($cust['Description'] ?? $cust['Code']) ?>
</option>

      <?php endforeach; ?>
    </select>
  </form>
</nav>
<main class="glass-main">
