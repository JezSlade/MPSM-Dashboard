<?php declare(strict_types=1);
// includes/navigation.php — Pure view, no headers/CORS

require_once __DIR__ . '/env_parser.php';
require_once __DIR__ . '/api_client.php';

try {
  $resp = api_request('Customer/GetCustomers', [
    'DealerCode' => DEALER_CODE,
    'PageNumber' => 1,
    'PageRows'   => 9999,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
  ]);
  $list = ($resp['status'] === 200 && is_array($resp['data']['Result'] ?? null))
        ? $resp['data']['Result']
        : [];
} catch (RuntimeException $e) {
  error_log('Nav load failed: ' . $e->getMessage());
  $list = [];
}
?>
<nav class="main-nav">
  <label for="customer-select" class="nav-label">Customer:</label>
  <select id="customer-select" name="CustomerCode" class="nav-select">
    <?php foreach ($list as $cust):
      $code = (string)($cust['CustomerCode'] ?? '');
      $desc = (string)($cust['Description']  ?? '');
      if ($code === '' && $desc === '') continue;
      $label = $desc !== '' ? $desc : $code;
    ?>
      <option value="<?= htmlspecialchars($code, ENT_QUOTES) ?>">
        <?= htmlspecialchars($label, ENT_QUOTES) ?>
      </option>
    <?php endforeach; ?>
  </select>
</nav>
