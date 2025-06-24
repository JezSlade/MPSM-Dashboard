<?php
// cards/CustomersCard.php — PHP-rendered customer table with global selection
declare(strict_types=1);

// 1) Bootstrap env and API client
require_once __DIR__ . '/../includes/card_base.php';    // loads .env, defines DEALER_CODE, etc.
require_once __DIR__ . '/../includes/api_client.php';  // defines api_request()

// 2) Read current selection from cookie (for highlighting)
$selectedCustomer = $_COOKIE['customer'] ?? '';

// 3) Fetch all customers via API
try {
    $resp = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => PHP_INT_MAX,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    $data = $resp['data'] ?? $resp;
} catch (RuntimeException $e) {
    $data = [];
}

// Normalize the list
$customers = $data['items'] ?? $data['Result'] ?? $data;
?>
<div
  id="CustomersCard"
  class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600"
>
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-xl font-semibold">Customers</h2>
  </header>

  <div class="overflow-auto">
    <table class="min-w-full divide-y divide-gray-700 text-sm">
      <thead class="bg-gray-800 text-white">
        <tr>
          <th class="px-4 py-2 text-left">Customer Code</th>
          <th class="px-4 py-2 text-left">Description</th>
        </tr>
      </thead>
      <tbody class="bg-gray-700 divide-y divide-gray-600">
        <?php foreach ($customers as $c): 
          $code = htmlspecialchars($c['CustomerCode'] ?? '', ENT_QUOTES);
          $desc = htmlspecialchars($c['Description']  ?? '', ENT_QUOTES);
          $isSelected = ($code === $selectedCustomer);
          $rowClass = $isSelected 
            ? 'bg-cyan-700 text-white' 
            : 'hover:bg-gray-600 text-gray-200';
        ?>
        <tr data-customer="<?= $code ?>" class="<?= $rowClass ?> cursor-pointer">
          <td class="px-4 py-2"><?= $code ?></td>
          <td class="px-4 py-2"><?= $desc ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Attach click handlers to set the customer cookie and reload
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('#CustomersCard tbody tr').forEach(row => {
    row.addEventListener('click', () => {
      const code = row.dataset.customer;
      if (!code) return;
      document.cookie = 'customer=' + encodeURIComponent(code) + ';path=/';
      window.location.reload();
    });
  });
});
</script>
