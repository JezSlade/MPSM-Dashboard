<?php
// includes/navigation.php
// -------------------------------------------------------------------
// Renders exactly one customer dropdown for the SPA.
// This is a server-side view include—no CORS or header modifications here.
// -------------------------------------------------------------------
declare(strict_types=1);

// 1) Environment and API client (no CORS or header functions)
require_once __DIR__ . '/env_parser.php';
require_once __DIR__ . '/../api_client.php';    // assumes auth.php already loaded in index

// 2) Fetch customer list
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
  <label for="customer-select" class="nav-label">Customer:</label>
  <select id="customer-select" name="CustomerCode" class="nav-select">
    <?php foreach ($customers as $cust):
        $code = isset($cust['CustomerCode']) ? (string)$cust['CustomerCode'] : '';
        $desc = isset($cust['Description'])   ? (string)$cust['Description']   : '';
        if ($code === '' && $desc === '') {
            continue;
        }
        $label = $desc !== '' ? $desc : $code;
    ?>
      <option value="<?= htmlspecialchars($code, ENT_QUOTES) ?>">
        <?= htmlspecialchars($label, ENT_QUOTES) ?>
      </option>
    <?php endforeach; ?>
  </select>
</nav>
