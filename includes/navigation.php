<?php
// includes/navigation.php
// -------------------------------------------------------------------
// Customer dropdown with etched glassmorphic + neon CMYK accents.
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
<nav class="flex items-center p-4
             bg-white bg-opacity-10 backdrop-blur-md
             border-b border-white border-opacity-20">
  <label for="customer-select" class="text-white font-medium mr-3">Customer:</label>
  <select id="customer-select" name="CustomerCode"
    class="flex-1 p-2 rounded-md
           bg-white bg-opacity-20 text-white
           focus:outline-none focus:ring-2 focus:ring-cyan-400
           hover:bg-opacity-30 transition">
    <?php foreach ($customers as $cust):
        $code = isset($cust['CustomerCode']) ? (string)$cust['CustomerCode'] : '';
        $desc = isset($cust['Description'])   ? (string)$cust['Description']   : '';
        if ($code === '' && $desc === '') continue;
        $label = $desc !== '' ? $desc : $code;
    ?>
      <option value="<?= htmlspecialchars($code, ENT_QUOTES) ?>"
        class="bg-gray-800 text-white hover:bg-gray-700">
        <?= htmlspecialchars($label, ENT_QUOTES) ?>
      </option>
    <?php endforeach; ?>
  </select>
</nav>
