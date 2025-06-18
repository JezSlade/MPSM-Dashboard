<?php declare(strict_types=1);
// /includes/navigation.php

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
    // 3) Call the internal API
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', $payload);

    // 4) Handle API‐level validation errors
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'Unknown API error');
    }

    $customers = $resp['Result'] ?? [];
    $error     = '';
} catch (\Throwable $e) {
    $customers = [];
    $error     = $e->getMessage();
}
?>
<div class="nav-dropdown-card">
  <?php if ($error !== ''): ?>
    <div class="nav-error">
      Error loading customers: <?= htmlspecialchars($error) ?>
    </div>
  <?php else: ?>
    <div class="nav-select-container" style="display:flex; gap:0.5rem; align-items:center;">
      <input
        type="text"
        id="nav-customer-search"
        class="customer-select"
        style="flex:1;"
        placeholder="Type to filter…"
        autocomplete="off"
      >
      <select
        id="nav-customer-select"
        class="customer-select"
        style="flex:2;"
      >
        <option value="" disabled selected>— choose a customer —</option>
        <?php foreach ($customers as $cust): 
          $code = htmlspecialchars($cust['Code'] ?? '');
          $name = htmlspecialchars($cust['Description'] ?? $cust['Name'] ?? $code);
        ?>
          <option value="<?= $code ?>"><?= $name ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>
</div>

<script>
// 5) Filter the dropdown as the user types
(function() {
  const input  = document.getElementById('nav-customer-search');
  const select = document.getElementById('nav-customer-select');
  input.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    Array.from(select.options).forEach(opt => {
      if (!opt.value) return; // placeholder
      const text = opt.text.toLowerCase();
      opt.style.display = text.includes(filter) ? '' : 'none';
    });
  });

  // 6) When selection changes, reload with ?customer=
  select.addEventListener('change', function() {
    window.location.search = '?customer=' + encodeURIComponent(this.value);
  });
})();
</script>
