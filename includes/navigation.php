<?php
// includes/navigation.php
// -------------------------------------------------------------------
// Renders exactly one customer dropdown for the SPA.
// -------------------------------------------------------------------
declare(strict_types=1);
require_once __DIR__ . '/env_parser.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cors.php';    send_cors_headers();
require_once __DIR__ . '/logger.php';  log_request();
require_once __DIR__ . '/api_client.php';

try {
    $resp = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => 9999,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    $customers = $resp['Result'] ?? [];
} catch (RuntimeException $e) {
    error_log('Nav load failed: ' . $e->getMessage());
    $customers = [];
}
?>
<nav class="main-nav">
  <label for="customer-select">Customer:</label>
  <select id="customer-select" name="CustomerCode">
    <?php foreach ($customers as $cust): ?>
      <option value="<?= htmlspecialchars($cust['CustomerCode'], ENT_QUOTES) ?>">
        <?= htmlspecialchars($cust['Description'], ENT_QUOTES) ?>
      </option>
    <?php endforeach; ?>
  </select>
</nav>
