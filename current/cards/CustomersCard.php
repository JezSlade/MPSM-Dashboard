<?php
// cards/CustomersCard.php — Preserve customer selection and keep all functionality
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// 1) Read the current selection up-front
$selected = htmlspecialchars($_GET['customer'] ?? '', ENT_QUOTES);

// Card identifier
$cardKey = 'CustomersCard';

// Read card settings from cookies (or defaults)
$cacheEnabledFlag     = isset($_COOKIE["{$cardKey}_cache_enabled"])     ? (bool)$_COOKIE["{$cardKey}_cache_enabled"]     : true;
$indicatorDisplayFlag = isset($_COOKIE["{$cardKey}_indicator_display"]) ? (bool)$_COOKIE["{$cardKey}_indicator_display"] : true;
$ttlMinutes           = isset($_COOKIE["{$cardKey}_ttl_minutes"])       ? max(1,(int)$_COOKIE["{$cardKey}_ttl_minutes"]) : 5;
$cacheTTL             = $ttlMinutes * 60;

// Build cache file path
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

// If caching disabled, remove any existing cache
if (!$cacheEnabledFlag && file_exists($cacheFile)) {
    @unlink($cacheFile);
}

// Determine cache status
$cacheExists = $cacheEnabledFlag && file_exists($cacheFile);
$cacheAge    = $cacheExists ? (time() - filemtime($cacheFile)) : null;
$cacheRem    = $cacheExists ? max(0, $cacheTTL - $cacheAge)           : 0;
$cachePct    = $cacheExists ? ($cacheRem / $cacheTTL) * 100          : 0;

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

// Count for snapshot
$count = count($rows);
?>

<div id="<?= $cardKey ?>" class="bg-gray-800/60 backdrop-blur-md border border-gray-600 rounded-lg shadow-lg overflow-hidden mx-auto max-w-4xl">

  <header class="relative px-6 py-3 bg-gray-700 border-b border-gray-600 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-white">Customers</h2>
    <div class="flex items-center space-x-2">
      <!-- Settings -->
      <button id="<?= $cardKey ?>_settings_btn" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600">
        <i data-feather="sliders" class="text-yellow-400 h-5 w-5"></i>
      </button>
      <!-- Minimize -->
      <button id="<?= $cardKey ?>_minimize_btn" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600">
        <i data-feather="chevron-up" class="text-white h-5 w-5"></i>
      </button>
      <!-- Refresh (preserves ?customer) -->
      <form method="get" class="inline-block">
        <input type="hidden" name="customer" value="<?= $selected ?>">
        <button type="submit" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600" title="Refresh">
          <i data-feather="refresh-ccw" class="text-cyan-300 h-5 w-5"></i>
        </button>
      </form>
    </div>
  </header>

  <?php if ($indicatorDisplayFlag): ?>
  <div class="px-6 pt-3">
    <div class="w-full h-2 bg-gray-600 rounded overflow-hidden">
      <div id="<?= $cardKey ?>_cache_bar" class="h-full bg-green-400 transition-all duration-1000 ease-linear" style="width: <?= $cachePct ?>%;"></div>
    </div>
    <div class="mt-1 flex justify-between text-xs text-gray-300 px-6">
      <span id="<?= $cardKey ?>_cache_age"><?= $cacheExists ? "{$cacheAge}s ago" : 'Cached: none' ?></span>
      <span id="<?= $cardKey ?>_cache_rem"><?= $cacheExists ? "Refresh in {$cacheRem}s" : '' ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- restored id for JS toggle -->
  <div id="<?= $cardKey ?>_body" class="p-6">
    <?php if ($error): ?>
      <div class="text-red-400 mb-4">Failed to load: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <?php
    renderDataTable(
      $rows,
      [
        'columns'               => [
          'CustomerCode' => 'Code',
          'Description'  => 'Description',
        ],
        'defaultVisibleColumns' => ['Description'],
        'defaultSort'           => 'Description',
        'rowsPerPage'           => 15,
        'searchable'            => true,
        'rowSelectKey'          => 'CustomerCode',
        'rowSelectParam'        => 'customer',
      ]
    );
    ?>
  </div>

  <div id="<?= $cardKey ?>_snapshot" class="hidden p-6 flex items-center space-x-4 bg-gray-700 cursor-pointer">
    <i data-feather="users" class="h-12 w-12 text-cyan-400"></i>
    <div>
      <div class="text-lg font-semibold text-white"><?= $selected ?: '—' ?></div>
      <div class="text-sm text-gray-300">Total: <?= $count ?></div>
    </div>
  </div>
</div>

<script>
if (window.feather) feather.replace();

(function(){
  // Cache‐bar animation
  const ttl    = <?= $cacheTTL ?>;
  let rem      = <?= $cacheRem ?>;
  const bar    = document.getElementById('<?= $cardKey ?>_cache_bar');
  const ageLbl = document.getElementById('<?= $cardKey ?>_cache_age');
  const remLbl = document.getElementById('<?= $cardKey ?>_cache_rem');
  (function tick(){
    if (rem <= 0) {
      bar.style.width = '0%';
      bar.className   = 'h-full bg-red-500';
      ageLbl.textContent = `Expired ${-rem}s ago`;
      remLbl.textContent = '';
      return;
    }
    const pct = rem/ttl*100;
    bar.style.width = pct + '%';
    bar.className   = 'h-full ' + (pct>50?'bg-green-400':pct>20?'bg-yellow-400':'bg-red-500');
    ageLbl.textContent = `${ttl-rem}s ago`;
    remLbl.textContent = `Refresh in ${rem}s`;
    rem--; setTimeout(tick,1000);
  })();

  // Minimize toggle
  const minBtn = document.getElementById('<?= $cardKey ?>_minimize_btn'),
        body   = document.getElementById('<?= $cardKey ?>_body'),
        snap   = document.getElementById('<?= $cardKey ?>_snapshot');
  let minimized = false;
  minBtn.addEventListener('click', ()=>{
    minimized = !minimized;
    body.style.display = minimized ? 'none':'';
    snap.style.display = minimized ? 'flex':'none';
    const ic = minBtn.querySelector('i');
    ic.setAttribute('data-feather', minimized?'chevron-down':'chevron-up');
    feather.replace();
  });

  // Settings panel toggle
  const setBtn   = document.getElementById('<?= $cardKey ?>_settings_btn'),
        setPanel = document.getElementById('<?= $cardKey ?>_settings_panel');
  setBtn.addEventListener('click', e => { e.stopPropagation(); setPanel.classList.toggle('hidden'); });
  document.addEventListener('click', e => {
    if (!setPanel.classList.contains('hidden') &&
        !setPanel.contains(e.target) &&
        !setBtn.contains(e.target)) {
      setPanel.classList.add('hidden');
    }
  });

  // (Your existing settings‐checkbox handlers here...)
})();
</script>
