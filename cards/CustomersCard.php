<?php
// cards/CustomersCard.php
declare(strict_types=1);

// 1) Bootstrap card UI, env, auth & API client
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/api_client.php';

// 2) Open card wrapper & header (with settings button)
card_base_start('CustomersCard', 'Customers');

// 3) Read selected customer for row highlighting
$selected = $_COOKIE['customer'] ?? '';

// 4) Fetch customer list
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
    echo '<p class="text-red-400">Error loading customers.</p>';
    card_base_end('CustomersCard');
    return;
}

// 5) Normalize payload
$customers = $data['items'] ?? $data['Result'] ?? $data;

// 6) Render table
echo '<div class="overflow-auto">';
echo '<table class="min-w-full divide-y divide-gray-700 text-sm">';
echo '<thead class="bg-gray-800 text-white"><tr>'
   . '<th class="px-4 py-2 text-left">Customer Code</th>'
   . '<th class="px-4 py-2 text-left">Description</th>'
   . '</tr></thead>';
echo '<tbody class="bg-gray-700 divide-y divide-gray-600">';
foreach ($customers as $c) {
    $code = htmlspecialchars($c['CustomerCode'] ?? '', ENT_QUOTES);
    $desc = htmlspecialchars($c['Description']  ?? '', ENT_QUOTES);
    $cls  = $code === $selected
          ? 'bg-cyan-700 text-white'
          : 'hover:bg-gray-600 text-gray-200';
    echo "<tr data-customer=\"{$code}\" class=\"{$cls} cursor-pointer\">";
    echo "<td class=\"px-4 py-2\">{$code}</td>";
    echo "<td class=\"px-4 py-2\">{$desc}</td>";
    echo "</tr>";
}
echo '</tbody></table></div>';

// 7) Inject row-click script
echo <<<JS
<script>
document.addEventListener('DOMContentLoaded', ()=> {
  document.querySelectorAll('#CustomersCard tbody tr').forEach(row => {
    row.addEventListener('click', ()=> {
      document.cookie = 'customer=' + encodeURIComponent(row.dataset.customer) + ';path=/';
      window.location.reload();
    });
  });
});
</script>
JS;

// 8) Close card wrapper & footer
card_base_end('CustomersCard');
