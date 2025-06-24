<?php
// includes/navigation.php
// -------------------------------------------------------------------
// Renders exactly one customer dropdown for the SPA.
// Safely handles null or missing values to avoid htmlspecialchars() errors.
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
    $customers = is_array($resp['Result'] ?? null) ? $resp['Result'] : [];
} catch (RuntimeException $e) {
    error_log('Nav load failed: ' . $e->getMessage());
    $customers = [];
}
?>
<nav class="main-nav">
  <label for="customer-select">Customer:</label>
  <select id="customer-select" name="CustomerCode">
    <?php foreach ($customers as $cust):
        // Cast to string and skip invalid entries
        $code = isset($cust['CustomerCode']) ? (string)$cust['CustomerCode'] : '';
        $desc = isset($cust['Description'])  ? (string)$cust['Description']  : '';
        if ($code === '' && $desc === '') {
            continue;
        }
        // Use description if present, else fallback to code
        $label = $desc !== '' ? $desc : $code;
    ?>
      <option value="<?= htmlspecialchars($code, ENT_QUOTES) ?>">
        <?= htmlspecialchars($label, ENT_QUOTES) ?>
      </option>
    <?php endforeach; ?>
  </select>
</nav>
