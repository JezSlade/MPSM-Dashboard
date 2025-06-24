<?php
// cards/CustomersCard.php — Adds “Refresh interval” setting alongside caching toggles
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

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
?>

<div id="<?= $cardKey ?>" class="bg-gray-800/60 backdrop-blur-md border border-gray-600 rounded-lg shadow-lg overflow-hidden mx-auto max-w-4xl">
  <header class="relative px-6 py-3 bg-gray-700 border-b border-gray-600">
    <!-- Title and buttons -->
    <div class="flex justify-between items-center">
      <h2 class="text-xl font-semibold text-white">Customers</h2>
      <div class="flex items-center space-x-2">
        <!-- Card settings -->
        <button id="<?= $cardKey ?>_settings_btn" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition" aria-label="Card settings">
          <i data-feather="sliders" class="text-yellow-400 h-5 w-5"></i>
        </button>
        <!-- Minimize -->
        <button id="<?= $cardKey ?>_minimize_btn" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition" aria-label="Minimize card">
          <i data-feather="chevron-up" class="text-white h-5 w-5"></i>
        </button>
        <!-- Refresh -->
        <form method="get">
          <button type="submit" class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition" title="Refresh">
            <i data-feather="refresh-ccw" class="text-cyan-300 h-5 w-5"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Cache progress bar & labels -->
    <?php if ($indicatorDisplayFlag): ?>
    <div class="mt-2 w-full h-2 bg-gray-600 rounded overflow-hidden">
      <div id="<?= $cardKey ?>_cache_bar" class="h-full bg-green-400 transition-all duration-1000 ease-linear" style="width: <?= $cachePct ?>%;"></div>
    </div>
    <div class="mt-1 flex justify-between text-xs text-gray-300">
      <span id="<?= $cardKey ?>_cache_age"><?= $cacheExists ? "{$cacheAge}s ago" : 'No cache' ?></span>
      <span id="<?= $cardKey ?>_cache_rem"><?= $cacheExists ? "Refresh in {$cacheRem}s" : '' ?></span>
    </div>
    <?php endif; ?>

    <!-- Card Settings Panel -->
    <div id="<?= $cardKey ?>_settings_panel" class="hidden absolute right-6 top-16 w-64 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-4 z-20">
      <h3 class="text-white font-semibold mb-3">Card Settings</h3>

      <label class="flex items-center text-gray-200 mb-3">
        <input type="checkbox" id="<?= $cardKey ?>_toggle_cache" class="mr-2 form-checkbox h-4 w-4 text-cyan-500"
          <?= $cacheEnabledFlag ? 'checked' : '' ?> />
        Enable caching
      </label>

      <label class="flex items-center text-gray-200 mb-3">
        <input type="checkbox" id="<?= $cardKey ?>_toggle_indicator" class="mr-2 form-checkbox h-4 w-4 text-cyan-500"
          <?= $indicatorDisplayFlag ? 'checked' : '' ?> />
        Show cache indicator
      </label>

      <div>
        <label for="<?= $cardKey ?>_ttl_input" class="block text-gray-300 mb-1">Refresh interval (minutes):</label>
        <input
          type="number"
          id="<?= $cardKey ?>_ttl_input"
          min="1"
          class="w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"
          value="<?= $ttlMinutes ?>"
        />
      </div>
    </div>
  </header>

  <div id="<?= $cardKey ?>_body" class="p-6">
    <?php if ($error): ?>
      <div class="text-red-400 mb-4">Failed to load: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
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

(function(){
  // Cache bar animation
  const ttl     = <?= $cacheTTL ?>;
  let remaining = <?= $cacheRem ?>;
  const bar     = document.getElementById('<?= $cardKey ?>_cache_bar');
  const ageLbl  = document.getElementById('<?= $cardKey ?>_cache_age');
  const remLbl  = document.getElementById('<?= $cardKey ?>_cache_rem');

  function updateBar(){
    if (remaining <= 0){
      bar.style.width = '0%';
      bar.className = 'h-full bg-red-500';
      ageLbl.textContent = remaining < 0 ? `Stale by ${-remaining}s` : 'No cache';
      remLbl.textContent = '';
      return;
    }
    const pct = (remaining / ttl) * 100;
    bar.style.width = pct + '%';
    bar.className = 'h-full ' + (pct>50?'bg-green-400':pct>20?'bg-yellow-400':'bg-red-500');
    ageLbl.textContent = `${ttl - remaining}s ago`;
    remLbl.textContent = `Refresh in ${remaining}s`;
    remaining--;
    setTimeout(updateBar, 1000);
  }
  updateBar();

  // Minimize toggle
  const minBtn = document.getElementById('<?= $cardKey ?>_minimize_btn');
  const body   = document.getElementById('<?= $cardKey ?>_body');
  let minimized = false;
  minBtn.addEventListener('click', () => {
    minimized = !minimized;
    body.style.display = minimized ? 'none' : '';
    const icon = minBtn.querySelector('i');
    icon.setAttribute('data-feather', minimized ? 'chevron-down' : 'chevron-up');
    feather.replace();
  });

  // Settings panel toggle
  const setBtn   = document.getElementById('<?= $cardKey ?>_settings_btn');
  const setPanel = document.getElementById('<?= $cardKey ?>_settings_panel');
  setBtn.addEventListener('click', () => setPanel.classList.toggle('hidden'));

  // Settings controls
  document.getElementById('<?= $cardKey ?>_toggle_cache').addEventListener('change', function(){
    document.cookie = "<?= $cardKey ?>_cache_enabled=" + (this.checked?1:0) + ";path=/";
    location.reload();
  });
  document.getElementById('<?= $cardKey ?>_toggle_indicator').addEventListener('change', function(){
    document.cookie = "<?= $cardKey ?>_indicator_display=" + (this.checked?1:0) + ";path=/";
    location.reload();
  });
  document.getElementById('<?= $cardKey ?>_ttl_input').addEventListener('change', function(){
    const mins = Math.max(1, parseInt(this.value,10)||5);
    document.cookie = "<?= $cardKey ?>_ttl_minutes=" + mins + ";path=/";
    location.reload();
  });

})();
</script>
