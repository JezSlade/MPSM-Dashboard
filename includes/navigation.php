<?php declare(strict_types=1);
// /includes/navigation.php

// 0) Bail out immediately for API or internal sub‐requests
if (
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0
    || basename($_SERVER['SCRIPT_NAME']) !== 'index.php'
) {
    return;
}

// 1) Shared API helpers + config loader
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Build GetCustomersRequest payload
$payload = [
    'DealerCode' => $config['DEALER_CODE'] ?? '',
    'PageNumber' => 1,
    'PageRows'   => 2147483647,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
];

try {
    // 3) Fetch the list
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', $payload);
    if (!empty($resp['Errors'])) {
        throw new \Exception($resp['Errors'][0]['Description'] ?? 'Error');
    }
    $customers = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    $customers = [];
    $error     = $e->getMessage();
}
?>
<div class="nav-dropdown-container">
  <?php if (!empty($error)): ?>
    <div class="nav-error"><?= htmlspecialchars($error) ?></div>
  <?php else: ?>
    <input
      type="text"
      id="nav-customer-search"
      class="customer-search-input"
      placeholder="Type to filter…"
      autocomplete="off"
    >
    <select id="nav-customer-select" class="customer-dropdown">
      <option value="" disabled selected>— choose a customer —</option>
      <?php foreach ($customers as $cust):
        $code = htmlspecialchars($cust['Code'] ?? '');
        $name = htmlspecialchars($cust['Description'] ?? $cust['Name'] ?? $code);
      ?>
        <option value="<?= $code ?>"><?= $name ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
</div>

<script>
// filter the <select> based on the text input
document.addEventListener('DOMContentLoaded', function(){
  const input  = document.getElementById('nav-customer-search');
  const select = document.getElementById('nav-customer-select');
  input.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    Array.from(select.options).forEach(opt => {
      if (!opt.value) return;
      opt.style.display = opt.text.toLowerCase().includes(q) ? '' : 'none';
    });
  });
  select.addEventListener('change', function(){
    window.location.search = '?customer=' + encodeURIComponent(this.value);
  });
});
</script>
