<?php declare(strict_types=1);
// includes/card_base.php
// -------------------------------------------------------------------
// Base bootstrap for all cards: load .env, auth, API client, and
// provide card_base_start() / card_base_end() for uniform card UI.
// -------------------------------------------------------------------

// 1) Load environment (env_parser.php reads and defines your .env constants)
require_once __DIR__ . '/env_parser.php';

// 2) Load auth & API client
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/api_client.php';

/**
 * Renders the opening HTML for a card, including header, cache indicator,
 * settings button & panel. Also injects the settings‐panel JS once on first call.
 *
 * @param string $cardKey Unique identifier for this card (used in IDs & cookies)
 * @param string $title   Card title to display
 */
function card_base_start(string $cardKey, string $title): void
{
    static $jsInjected = false;

    // Inject settings‐panel JS once
    if (!$jsInjected) {
        echo <<<JS
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[id\$="_settingsBtn"]').forEach(btn => {
    const key = btn.id.replace('_settingsBtn','');
    const panel = document.getElementById(key + '_settingsPanel');
    btn.addEventListener('click', () => panel.classList.toggle('hidden'));
    const setCookie = (n,v) => document.cookie = \`\${n}=\${v};path=/\`;
    document.getElementById(key + '_cacheToggle')
      .addEventListener('change', e => { setCookie(key + '_cache_enabled', e.target.checked ? '1' : '0'); location.reload(); });
    document.getElementById(key + '_indicatorToggle')
      .addEventListener('change', e => { setCookie(key + '_indicator_display', e.target.checked ? '1' : '0'); location.reload(); });
    document.getElementById(key + '_ttlInput')
      .addEventListener('change', e => { setCookie(key + '_ttl_minutes', Math.max(1, e.target.value)); location.reload(); });
  });
});
</script>
JS;
        $jsInjected = true;
    }

    // Read settings from cookies
    $cacheOn       = isset($_COOKIE["{$cardKey}_cache_enabled"])     && $_COOKIE["{$cardKey}_cache_enabled"] === '1';
    $showIndicator = isset($_COOKIE["{$cardKey}_indicator_display"]) && $_COOKIE["{$cardKey}_indicator_display"] === '1';
    $ttlMinutes    = isset($_COOKIE["{$cardKey}_ttl_minutes"])       ? max(1,(int)$_COOKIE["{$cardKey}_ttl_minutes"]) : 5;

    // Precompute checkbox attributes
    $cacheChecked     = $cacheOn       ? ' checked' : '';
    $indicatorChecked = $showIndicator ? ' checked' : '';

    // Render opening wrapper and header
    echo <<<HTML
<div id="{$cardKey}" class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600" data-card-key="{$cardKey}">
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-xl font-semibold">{$title}</h2>
    <div class="flex items-center space-x-2">
HTML;
    if ($showIndicator) {
        $label = $cacheOn ? "{$ttlMinutes} min cache" : "No cache";
        echo "<span class=\"text-sm text-gray-400\">{$label}</span>";
    }
    echo <<<HTML
      <button id="{$cardKey}_settingsBtn" class="p-1 rounded-md bg-gray-700 hover:bg-gray-600 text-gray-200" aria-label="Card settings">
        <i data-feather="settings"></i>
      </button>
    </div>
  </header>

  <div id="{$cardKey}_settingsPanel" class="hidden bg-gray-800 border border-gray-600 rounded-md p-3 mb-3">
    <h3 class="text-gray-200 font-semibold mb-2">Settings</h3>
    <label class="flex items-center text-gray-200 mb-2">
      <input type="checkbox" id="{$cardKey}_cacheToggle" class="mr-2"{$cacheChecked}/> Enable Cache
    </label>
    <label class="flex items-center text-gray-200 mb-2">
      <input type="checkbox" id="{$cardKey}_indicatorToggle" class="mr-2"{$indicatorChecked}/> Show Indicator
    </label>
    <div class="flex items-center text-gray-200">
      <label for="{$cardKey}_ttlInput" class="mr-2">TTL (min):</label>
      <input type="number" id="{$cardKey}_ttlInput" min="1" value="{$ttlMinutes}" class="w-16 bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"/>
    </div>
  </div>
HTML;
}

/**
 * Renders the footer timestamp (if cache is enabled) and closes the card wrapper.
 *
 * @param string $cardKey Unique identifier used to read cache setting
 */
function card_base_end(string $cardKey): void
{
    $cacheOn = isset($_COOKIE["{$cardKey}_cache_enabled"]) && $_COOKIE["{$cardKey}_cache_enabled"] === '1';
    if ($cacheOn) {
        echo '<footer class="mt-4 text-right text-xs text-gray-500">';
        echo 'Updated ' . date('Y-m-d H:i');
        echo '</footer>';
    }
    echo '</div>'; // close card wrapper
}
