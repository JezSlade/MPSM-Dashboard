<?php
// cards/CustomersCard.php — Adds cache‐age indicator beside Refresh
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// Cache TTL must match api_client.php
$cacheTTL = 300;

// Prepare cache file path same as api_client
$path       = 'Customer/GetCustomers';
$body       = [
    'DealerCode' => DEALER_CODE,
    'PageNumber' => 1,
    'PageRows'   => 9999,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
];
$keySource  = $path . '|' . json_encode($body);
$cacheFile  = __DIR__ . '/../cache/' . sha1($keySource) . '.json';

$cacheExists = file_exists($cacheFile);
$cacheAge    = $cacheExists ? (time() - filemtime($cacheFile)) : null;
$cacheRem    = $cacheExists ? ($cacheTTL - $cacheAge) : null;

// Determine light color
if (!$cacheExists) {
    $dotColor = 'bg-gray-600';
    $statusText = 'No cache';
} elseif ($cacheAge < $cacheTTL * 0.5) {
    $dotColor = 'bg-green-400';
    $statusText = sprintf('Cached %ds ago (refresh in %ds)', $cacheAge, $cacheRem);
} elseif ($cacheAge < $cacheTTL) {
    $dotColor = 'bg-yellow-400';
    $statusText = sprintf('Cached %ds ago (refresh in %ds)', $cacheAge, $cacheRem);
} else {
    $dotColor = 'bg-red-500';
    $statusText = sprintf('Stale by %ds', abs($cacheRem));
}

// Fetch data
try {
  $resp = api_request($path, $body);
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

<div class="bg-gray-800/60 backdrop-blur-md border border-gray-600 rounded-lg
            shadow-lg overflow-hidden mx-auto max-w-4xl">
  <header class="flex justify-between items-center px-6 py-3
                 bg-gray-700 border-b border-gray-600">
    <h2 class="text-xl font-semibold text-white">Customers</h2>
    <div class="flex items-center space-x-4">
      <!-- Cache indicator -->
      <div class="flex items-center space-x-1">
        <span class="h-3 w-3 rounded-full <?= $dotColor ?>"></span>
        <span class="text-sm text-gray-300"><?= htmlspecialchars($statusText) ?></span>
      </div>
      <!-- Refresh button -->
      <form method="get">
        <button type="submit"
          class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
          title="Refresh">
          <i data-feather="refresh-ccw" class="text-cyan-300"></i>
        </button>
      </form>
    </div>
  </header>

  <div class="p-6">
    <?php if ($error): ?>
      <div class="text-red-400 mb-4">
        Failed to load: <?= htmlspecialchars($error, ENT_QUOTES) ?>
      </div>
    <?php endif; ?>

    <?php
    // Server‐rendered table via helper
    renderDataTable(
      $rows,
      [
        'defaultVisibleColumns' => ['Description'],
        'defaultSort'           => 'Description',
        'rowsPerPage'           => 15,
        'searchable'            => true,
      ]
    );
    ?>
  </div>
</div>

<script>
  if (window.feather) feather.replace();
</script>
