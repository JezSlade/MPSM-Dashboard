<?php
// cards/CustomersCard.php — Customers list with global selection
declare(strict_types=1);

require_once __DIR__ . '/../includes/env_parser.php';        // ← ADDED
parse_env_file(__DIR__ . '/../.env');                        // ← ADDED

require_once __DIR__ . '/../includes/card_base.php';         // now has access to DEALER_CODE
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// 1) Read selection from cookie → querystring
$selected = $_COOKIE['customer'] ?? ($_GET['customer'] ?? '');

// 2) Card settings
$cardKey               = 'CustomersCard';
$cacheEnabledFlag      = isset($_COOKIE["{$cardKey}_cache_enabled"])     ? (bool)$_COOKIE["{$cardKey}_cache_enabled"]     : true;
$indicatorDisplayFlag  = isset($_COOKIE["{$cardKey}_indicator_display"]) ? (bool)$_COOKIE["{$cardKey}_indicator_display"] : true;
$ttlMinutes            = isset($_COOKIE["{$cardKey}_ttl_minutes"])       ? max(1,(int)$_COOKIE["{$cardKey}_ttl_minutes"]) : 5;
$cacheTTL              = $ttlMinutes * 60;

// 3) Fetch customers via API
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

$customers = $data['items'] ?? $data['Result'] ?? $data;

// 4) Prepare rows for renderDataTable
$rows = array_map(fn($c) => [
    'CustomerCode' => $c['CustomerCode'] ?? '',
    'Description'  => $c['Description']  ?? '',
], $customers);
?>

<div
  id="<?= $cardKey ?>"
  class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600"
  data-card-key="<?= $cardKey ?>"
>
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-xl font-semibold">Customers</h2>
    <?php if ($indicatorDisplayFlag): ?>
      <span class="text-sm text-gray-400">
        <?= $cacheEnabledFlag ? "{$ttlMinutes} min cache" : 'No cache' ?>
      </span>
    <?php endif; ?>
  </header>

  <?php
    renderDataTable(
      $rows,
      [
        'columns'               => ['CustomerCode'=>'Customer Code','Description'=>'Description'],
        'sortable'              => false,
        'searchable'            => true,
        'rowsPerPage'           => 999,
        'rowSelectKey'          => 'CustomerCode',
        'rowSelectParam'        => 'customer',
        'defaultVisibleColumns' => ['Description'],
        'selectedValue'         => $selected,
      ]
    );
  ?>

  <?php if ($cacheEnabledFlag): ?>
    <footer class="mt-4 text-right text-xs text-gray-500">
      Updated <?= date('Y-m-d H:i') ?>
    </footer>
  <?php endif; ?>
</div>
