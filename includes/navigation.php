<?php declare(strict_types=1);
// /includes/navigation.php

// 1) Shared helpers + config loader
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
    // 3) Call the API
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', $payload);

    // 4) Handle API‐level errors
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
    <label for="nav-customer-search" class="nav-label">Select Customer:</label>
    <input
      type="text"
      id="nav-customer-search"
      class="nav-search-input"
      placeholder="Type to filter…"
      autocomplete="off"
    >
    <select id="nav-customer-select" class="nav-select">
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
// Filter the dropdown as the user types
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

  // When selection changes, you can trigger view reload, e.g.:
  select.addEventListener('change', function() {
    const customerCode = this.value;
    // e.g., reload the page with new customer filter:
    window.location.search = '?customer=' + encodeURIComponent(customerCode);
  });
})();
</script>

<style>
.nav-dropdown-card {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.nav-label {
  color: var(--text-light);
  font-weight: bold;
}
.nav-search-input,
.nav-select {
  padding: 0.5rem;
  border: none;
  border-radius: 4px;
  background: var(--bg-light);
  color: var(--text-light);
}
.nav-search-input:focus,
.nav-select:focus {
  outline: 2px solid var(--text-light);
}
</style>
