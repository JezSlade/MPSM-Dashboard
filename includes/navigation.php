<?php declare(strict_types=1);
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
<nav class="flex items-center p-4 bg-white/10 backdrop-blur-md border-b border-white/20">
  <label for="customer-select" class="text-white font-medium mr-3">Customer:</label>
  <select id="customer-select" name="CustomerCode"
    class="w-48 p-2 rounded-md bg-white/20 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 hover:bg-white/30 transition">
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
