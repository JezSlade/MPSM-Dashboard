<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// PHP‐only Customers card using the existing renderDataTable helper.
// Fetches via api_request() and renders a searchable, sortable, pageable table.
// -------------------------------------------------------------------

declare(strict_types=1);

// Card wrapper (glassmorphic container, no nav/header logic)
require_once __DIR__ . '/../includes/card_base.php';

// Core API guardrails
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';    send_cors_headers();
require_once __DIR__ . '/../includes/logger.php';  log_request();
require_once __DIR__ . '/../includes/api_client.php';

// Table helper
require_once __DIR__ . '/../includes/table_helper.php';

// Fetch all customers server-side
try {
    $response = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => 9999,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    $rows  = is_array($response['Result'] ?? null) ? $response['Result'] : [];
    $error = null;
} catch (RuntimeException $e) {
    $rows  = [];
    $error = $e->getMessage();
}

// Define which columns to show and their headers
$columns = [
    'CustomerCode' => 'Customer Code',
    'Description'  => 'Description',
];
?>

<div class="card customers-card max-w-4xl mx-auto mb-6">
  <header class="card-header flex justify-between items-center px-4 py-2">
    <h2 class="text-xl font-semibold text-white">Customers</h2>
    <form method="get" action="">
      <button type="submit"
        class="p-2 rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 transition"
        title="Refresh Customers">
        <i data-feather="refresh-ccw" class="text-magenta-400"></i>
      </button>
    </form>
  </header>

  <div class="card-body p-4">
    <?php if ($error): ?>
      <div class="text-center text-red-400 mb-4">
        Failed to load customers: <?= htmlspecialchars($error, ENT_QUOTES) ?>
      </div>
    <?php endif; ?>

    <?php
    // Render the data table with search/sort/pagination
    // We load all rows at once; searchable = true, rowsPerPage = 15
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

<script>
  // Re-initialize Feather icons
  if (window.feather) {
    feather.replace({ 'stroke-width': 2, width: '1em', height: '1em' });
  }
</script>
