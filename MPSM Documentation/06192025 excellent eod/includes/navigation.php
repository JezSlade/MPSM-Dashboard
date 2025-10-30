<?php declare(strict_types=1);
// /includes/navigation.php

// Don’t render on API calls
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// Fetch all customers
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

// Determine current selection
$currentCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? '';
$currentName = '';
foreach ($customers as $cust) {
    if (($cust['Code'] ?? '') === $currentCode) {
        $currentName = $cust['Description'] 
                     ?? $cust['Name'] 
                     ?? $currentCode;
        break;
    }
}
?>
<!-- NAV BAR (rendered under header.php) -->
<div class="flex items-center px-4 py-2 bg-gray-800 bg-opacity-50 backdrop-blur-sm space-x-4">
  <!-- Fixed-width searchable dropdown -->
  <div class="w-64 flex-shrink-0">
    <label for="nav-customer-combobox" class="sr-only">Choose Customer</label>
    <input
      list="nav-customer-list"
      id="nav-customer-combobox"
      class="h-8 w-full text-sm bg-gray-800 dark:bg-gray-700 text-gray-200 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
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

  <!-- (Keep any other nav buttons or links here if needed) -->
</div>

<script>
// Wire up the customer combobox
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('nav-customer-combobox');
  const options = document.getElementById('nav-customer-list').options;

  input.addEventListener('change', () => {
    const chosen = Array.from(options)
                        .find(o => o.value === input.value);
    const code = chosen ? chosen.dataset.code : '';
    if (code) {
      document.cookie = `customer=${encodeURIComponent(code)};path=/;max-age=${60*60*24*365}`;
      // Reload preserving any other query params if desired
      window.location.href = `${window.location.pathname}?customer=${encodeURIComponent(code)}`;
    }
  });
});
</script>
