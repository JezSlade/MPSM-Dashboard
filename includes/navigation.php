<?php declare(strict_types=1);
// /includes/navigation.php

// Bail out on API or non-index.php requests
if (
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0
    || basename($_SERVER['SCRIPT_NAME']) !== 'index.php'
) {
    return;
}

require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// Fetch customers via API
$payload = [
    'DealerCode' => $config['DEALER_CODE'] ?? '',
    'PageNumber' => 1,
    'PageRows'   => 2147483647,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
];
try {
    $resp      = call_api($config, 'POST', 'Customer/GetCustomers', $payload);
    $customers = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    $customers = [];
}

// Determine current customer code & name
$currentCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? '';
$currentName = '';
foreach ($customers as $cust) {
    if (($cust['Code'] ?? '') === $currentCode) {
        $currentName = $cust['Description'] ?? $cust['Name'] ?? '';
        break;
    }
}
?>
<div class="nav-dropdown-container relative max-w-xs">
  <label for="nav-customer-combobox" class="sr-only">Select Customer</label>
  <input
    list="nav-customer-list"
    id="nav-customer-combobox"
    class="w-full text-xs bg-gray-800 dark:bg-gray-700 text-gray-200 border border-gray-600 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500"
    placeholder="— choose a customer —"
    value="<?= htmlspecialchars($currentName) ?>"
  />
  <datalist id="nav-customer-list">
    <?php foreach ($customers as $cust):
        $code = htmlspecialchars($cust['Code'] ?? '');
        $name = htmlspecialchars($cust['Description'] ?? $cust['Name'] ?? $code);
    ?>
      <option data-code="<?= $code ?>" value="<?= $name ?>"></option>
    <?php endforeach; ?>
  </datalist>
</div>

<script>
// Wire up the searchable combobox
document.addEventListener('DOMContentLoaded', function(){
  const input = document.getElementById('nav-customer-combobox');
  const list  = document.getElementById('nav-customer-list').options;

  input.addEventListener('input', function(){
    // No-op: datalist handles showing matches
  });

  input.addEventListener('change', function(){
    const val = this.value;
    let code = '';
    for (let opt of list) {
      if (opt.value === val) {
        code = opt.getAttribute('data-code');
        break;
      }
    }
    if (code) {
      document.cookie = `customer=${encodeURIComponent(code)};path=/;max-age=${60*60*24*365}`;
      window.location.search = '?customer=' + encodeURIComponent(code);
    }
  });
});
</script>
