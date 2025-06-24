<?php
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
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

<div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-lg 
            shadow-[0_0_10px_rgba(0,255,255,0.4),0_0_20px_rgba(255,0,255,0.3),0_0_30px_rgba(255,255,0,0.2)]
            overflow-hidden">
  <header class="flex justify-between items-center px-4 py-2
                 bg-white/20 border-b border-white/10">
    <h2 class="text-lg font-semibold text-white">Customers</h2>
    <form method="get">
      <button type="submit" class="p-2 rounded-md bg-white/20 hover:bg-white/30 transition" title="Refresh">
        <i data-feather="refresh-ccw" class="text-magenta-400"></i>
      </button>
    </form>
  </header>

  <div class="p-4">
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
