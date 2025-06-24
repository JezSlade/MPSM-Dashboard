<?php
// cards/CustomersCard.php — Server‐rendered via renderDataTable()
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

try {
  $resp = api_request('Customer/GetCustomers', [
    'DealerCode' => DEALER_CODE,
    'PageNumber' => 1,
    'PageRows'   => 9999,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
  ]);
  $rows  = ($resp['status'] === 200 && is_array($resp['data']['Result'] ?? null))
         ? $resp['data']['Result']
         : [];
  $error = null;
} catch (RuntimeException $e) {
  $rows  = [];
  $error = $e->getMessage();
}

$columns = [
  'CustomerCode' => 'Code',
  'Description'  => 'Description',
];
?>

<div class="card customers-card">
  <header class="card-header">
    <h2 class="text-lg font-semibold text-white">Customers</h2>
    <form method="get">
      <button type="submit" class="icon-btn" title="Refresh">
        <i data-feather="refresh-ccw" class="text-magenta-400"></i>
      </button>
    </form>
  </header>

  <div class="card-body">
    <?php if ($error): ?>
      <div class="text-red-400 mb-4">Failed to load: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <?php
    renderDataTable(
      $rows,
      [
        'columns'     => $columns,
        'defaultSort' => 'Description',
        'rowsPerPage' => 15,
        'searchable'  => true,
      ]
    );
    ?>
  </div>
</div>
