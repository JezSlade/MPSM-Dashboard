<?php
// cards/CustomersCard.php — Adds animated cache‐expiry progress bar
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// Cache TTL must match api_client.php
$cacheTTL = 300;

// Compute cache file path
$path      = 'Customer/GetCustomers';
$body      = [
    'DealerCode' => DEALER_CODE,
    'PageNumber'  => 1,
    'PageRows'    => 9999,
    'SortColumn'  => 'Description',
    'SortOrder'   => 'Asc',
];
$keySource = $path . '|' . json_encode($body);
$cacheFile = __DIR__ . '/../cache/' . sha1($keySource) . '.json';

$cacheExists = file_exists($cacheFile);
$cacheAge    = $cacheExists ? (time() - filemtime($cacheFile)) : null;
$cacheRem    = $cacheExists ? max(0, $cacheTTL - $cacheAge) : 0;
$cachePct    = $cacheExists ? ($cacheRem / $cacheTTL) * 100 : 0;

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
  <header class="px-6 py-3 bg-gray-700 border-b border-gray-600 flex flex-col space-y-2">
    <div class="flex justify-between items-center">
      <h2 class="text-xl font-semibold text-white">Customers</h2>
      <form method="get" class="flex items-center space-x-2">
        <button type="submit"
          class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
          title="Refresh">
          <i data-feather="refresh-ccw" class="text-cyan-300"></i>
        </button>
      </form>
    </div>

    <!-- Cache Progress Bar Container -->
    <div class="w-full h-2 bg-gray-600 rounded overflow-hidden">
      <div id="cache-bar" 
           class="h-full bg-green-400 transition-all duration-1000 ease-linear"
           style="width: <?= $cachePct ?>%;">
      </div>
    </div>
    <div class="flex justify-between text-xs text-gray-300">
      <span id="cache-age"><?= $cacheExists ? "{$cacheAge}s ago" : 'No cache' ?></span>
      <span id="cache-rem"><?= $cacheExists ? "Refresh in {$cacheRem}s" : '' ?></span>
    </div>
  </header>

  <div class="p-6">
    <?php if ($error): ?>
      <div class="text-red-400 mb-4">
        Failed to load: <?= htmlspecialchars($error, ENT_QUOTES) ?>
      </div>
    <?php endif; ?>

    <?php
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

// Animate the cache bar countdown
(function(){
  const ttl      = <?= $cacheTTL ?>;
  let remaining  = <?= $cacheRem ?>;
  const bar      = document.getElementById('cache-bar');
  const ageLabel = document.getElementById('cache-age');
  const remLabel = document.getElementById('cache-rem');

  function updateBar() {
    if (remaining <= 0) {
      bar.style.width = '0%';
      bar.className = 'h-full bg-red-500';
      ageLabel.textContent = remaining < 0 ? `Stale by ${-remaining}s` : 'No cache';
      remLabel.textContent = '';
      return;
    }
    const pct = (remaining / ttl) * 100;
    bar.style.width = pct + '%';
    // Color transition: green → yellow → red
    if (pct > 50) bar.className = 'h-full bg-green-400';
    else if (pct > 20) bar.className = 'h-full bg-yellow-400';
    else bar.className = 'h-full bg-red-500';

    ageLabel.textContent = `${ttl - remaining}s ago`;
    remLabel.textContent = `Refresh in ${remaining}s`;
    remaining--;
    setTimeout(updateBar, 1000);
  }

  updateBar();
})();
</script>
