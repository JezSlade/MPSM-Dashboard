<?php declare(strict_types=1);
// includes/card_base.php
// -------------------------------------------------------------------
// Base bootstrap for all cards: load .env, auth, API client, and
// provide card_base_start() / card_base_end() for uniform card UI
// with Feather icons, Tailwind styling, manual refresh, cache indicator,
// live countdown, and settings panel.
// -------------------------------------------------------------------

require_once __DIR__ . '/env_parser.php';   // loads and defines .env constants
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/api_client.php';

/**
 * Starts the card wrapper and renders header with title, cache status,
 * refresh button, countdown, and settings toggle.
 *
 * @param string $cardKey Unique ID for this card (used in IDs & cookies)
 * @param string $title   Display title of the card
 */
function card_base_start(string $cardKey, string $title): void
{
    static $jsInjected = false;

    // Read per-card settings from cookies
    $cacheOn       = isset($_COOKIE["{$cardKey}_cache_enabled"])     && $_COOKIE["{$cardKey}_cache_enabled"] === '1';
    $showIndicator = isset($_COOKIE["{$cardKey}_indicator_display"]) && $_COOKIE["{$cardKey}_indicator_display"] === '1';
    $ttlMinutes    = isset($_COOKIE["{$cardKey}_ttl_minutes"])       ? max(1, (int)$_COOKIE["{$cardKey}_ttl_minutes"]) : 5;
    $ttlSeconds    = $ttlMinutes * 60;

    // Determine cache-file status text
    $cacheFile   = __DIR__ . "/../cache/{$cardKey}.cache";
    $cacheStatus = is_readable($cacheFile)
        ? 'Cached at ' . date('H:i:s', filemtime($cacheFile))
        : 'No cache file';

    // Precompute checkbox attributes
    $cacheChecked     = $cacheOn       ? ' checked' : '';
    $indicatorChecked = $showIndicator ? ' checked' : '';

    // Inject card-level JS once
    if (!$jsInjected) {
        echo <<<JS
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof feather !== 'undefined') feather.replace();
  setTimeout(() => {
    document.querySelectorAll('[id\$="_settingsBtn"]').forEach(btn => {
      const key = btn.id.replace('_settingsBtn','');
      const panel = document.getElementById(key + '_settingsPanel');
      btn.addEventListener('click', e => { e.preventDefault(); panel.classList.toggle('hidden'); });

      const setCookie = (n,v) => document.cookie = \`\${n}=\${v};path=/\`;

      document.getElementById(key + '_cacheToggle')
        .addEventListener('change', e => { setCookie(key + '_cache_enabled', e.target.checked ? '1' : '0'); location.reload(); });
      document.getElementById(key + '_indicatorToggle')
        .addEventListener('change', e => { setCookie(key + '_indicator_display', e.target.checked ? '1' : '0'); location.reload(); });
      document.getElementById(key + '_ttlInput')
        .addEventListener('change', e => { setCookie(key + '_ttl_minutes', Math.max(1, e.target.value)); location.reload(); });

      document.getElementById(key + '_refreshBtn')
        .addEventListener('click', e => { e.preventDefault(); location.reload(); });

      const countdownEl = document.getElementById(key + '_countdown');
      if (countdownEl) {
        let sec = parseInt(countdownEl.dataset.seconds, 10);
        setInterval(() => {
          sec--;
          if (sec <= 0) return location.reload();
          countdownEl.textContent = sec + 's';
        }, 1000);
      }
    });
  }, 50);
});
</script>
JS;
        $jsInjected = true;
    }

    // Render the card wrapper and header
    echo <<<HTML
<div id="{$cardKey}" class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600" data-card-key="{$cardKey}">
  <header class="mb-4 flex items-center justify-between space-x-4">
    <h2 class="text-xl font-semibold text-gray-100">{$title}</h2>
    <div class="flex items-center space-x-3">
      <span class="text-sm text-gray-400">{$cacheStatus}</span>
      <!-- Manual refresh button -->
      <button id="{$cardKey}_refreshBtn" class="p-2 bg-gray-700 hover:bg-gray-600 rounded-md focus:outline-none" aria-label="Refresh">
        <i data-feather="refresh-cw" class="w-5 h-5 text-gray-200"></i>
      </button>
HTML;
    if ($showIndicator) {
        echo <<<HTML
      <span class="text-sm text-gray-400">{$ttlMinutes}&nbsp;min cache</span>
      <span id="{$cardKey}_countdown" data-seconds="{$ttlSeconds}" class="text-sm text-gray-400">{$ttlSeconds}s</span>
HTML;
    }
    echo <<<HTML
      <!-- Settings toggle button -->
      <button id="{$cardKey}_settingsBtn" class="p-2 bg-gray-700 hover:bg-gray-600 rounded-md focus:outline-none" aria-label="Settings">
        <i data-feather="settings" class="w-5 h-5 text-gray-200"></i>
      </button>
    </div>
  </header>

  <!-- Settings panel (hidden by default) -->
  <div id="{$cardKey}_settingsPanel" class="hidden bg-gray-800 border border-gray-600 rounded-md p-4 mb-4">
    <h3 class="text-gray-200 font-semibold mb-3">Settings</h3>
    <div class="space-y-2">
      <label class="flex items-center text-gray-200">
        <input type="checkbox" id="{$cardKey}_cacheToggle" class="form-checkbox mr-2"{$cacheChecked}/>
        Enable Cache
      </label>
      <label class="flex items-center text-gray-200">
        <input type="checkbox" id="{$cardKey}_indicatorToggle" class="form-checkbox mr-2"{$indicatorChecked}/>
        Show Indicator
      </label>
      <div class="flex items-center text-gray-200">
        <label for="{$cardKey}_ttlInput" class="mr-2">TTL (min):</label>
        <input type="number" id="{$cardKey}_ttlInput" min="1" value="{$ttlMinutes}" class="w-16 bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2 focus:outline-none"/>
      </div>
    </div>
  </div>
HTML;
}

/**
 * Ends the card wrapper and appends a footer timestamp if cache is enabled.
 *
 * @param string $cardKey Unique ID for this card
 */
function card_base_end(string $cardKey): void
{
    if (isset($_COOKIE["{$cardKey}_cache_enabled"]) && $_COOKIE["{$cardKey}_cache_enabled"] === '1') {
        echo '<footer class="mt-4 text-right text-xs text-gray-500">';
        echo 'Updated ' . date('Y-m-d H:i');
        echo '</footer>';
    }
    echo '</div>'; // close .glass-card wrapper
}
