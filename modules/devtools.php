<?php
// modules/devtools.php
// ────────────────────────────────────────────────────────────────────────────────
// “DevTools → Style Customizer” Module
// Must be included inside <div class="floating-module">…</div> in index.php.
// The only requirement here is that Neumorphic :root variables already exist
// in styles.css. This file no longer checks for BASE_PATH.

if (!defined('STDIN') && php_sapi_name() !== 'cli') {
    // Optional: permission check here if needed
    // For example: if (!user_is_sysop()) { die('Forbidden'); }
}

// No BASE_PATH check; we rely on __DIR__ for any includes if necessary.
?>
<div class="glass p-4 border border-gray-800 rounded space-y-4">
    <h2 class="text-2xl text-cyan-neon mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 
                     11-18 0 9 9 0 0118 0z"></path>
        </svg>
        DevTools
    </h2>
    <p class="mb-4">Developer settings and tools for MPSM.</p>

    <!-- ── Style Customizer Form ──────────────────────────────────────────────────── -->
    <div class="devtools-container">
        <h2>Style Customizer</h2>

        <!-- 1) Neumorphic Depth -->
        <div class="devtools-row">
            <label for="depthRange">Neumorphic Depth (<span id="depthValue">8</span>px):</label>
            <input
                type="range"
                id="depthRange"
                min="0"
                max="20"
                step="1"
                value="8"
                oninput="updateVar('--depth', this.value + 'px'); document.getElementById('depthValue').innerText = this.value;"
            >
            <span class="devtools-value" id="depthValueDisplay">8</span>
        </div>

        <!-- 2) Glass Opacity -->
        <div class="devtools-row">
            <label for="glassOpacityRange">Glass Opacity (0.1 – 1.0):</label>
            <input
                type="range"
                id="glassOpacityRange"
                min="0.1"
                max="1.0"
                step="0.1"
                value="0.8"
                oninput="updateVar('--glass-opacity', this.value); document.getElementById('opacityValue').innerText = this.value;"
            >
            <span class="devtools-value" id="opacityValue">0.8</span>
        </div>

        <!-- 3) Cyan Neon Color -->
        <div class="devtools-row">
            <label for="cyanColorPicker">Cyan Neon:</label>
            <input
                type="color"
                id="cyanColorPicker"
                value="#00FFFF"
                onchange="updateVar('--cyan-neon', this.value);"
            >
        </div>

        <!-- 4) Magenta Neon Color -->
        <div class="devtools-row">
            <label for="magentaColorPicker">Magenta Neon:</label>
            <input
                type="color"
                id="magentaColorPicker"
                value="#FF00FF"
                onchange="updateVar('--magenta-neon', this.value);"
            >
        </div>

        <!-- 5) Yellow Neon Color -->
        <div class="devtools-row">
            <label for="yellowColorPicker">Yellow Neon:</label>
            <input
                type="color"
                id="yellowColorPicker"
                value="#FFFF00"
                onchange="updateVar('--yellow-neon', this.value);"
            >
        </div>

        <!-- 6) Background Dark (optional) -->
        <div class="devtools-row">
            <label for="bgDarkPicker">Background Dark:</label>
            <input
                type="color"
                id="bgDarkPicker"
                value="#1E272E"
                onchange="updateVar('--bg-dark', this.value);"
            >
        </div>

        <!-- 7) Foreground Dark (cards/panels) (optional) -->
        <div class="devtools-row">
            <label for="fgDarkPicker">Foreground Dark:</label>
            <input
                type="color"
                id="fgDarkPicker"
                value="#2D3436"
                onchange="updateVar('--fg-dark', this.value);"
            >
        </div>

        <!-- Reset button -->
        <button class="devtools-reset" onclick="resetDefaults()">
            Reset to Default
        </button>
    </div>

    <!-- ── JavaScript for Live CSS‐Variable Updates ───────────────────────────────── -->
    <script>
    (function() {
      // Default values must match :root defaults in styles.css
      const defaults = {
        '--depth'        : '8px',
        '--glass-opacity': '0.8',
        '--cyan-neon'    : '#00FFFF',
        '--magenta-neon' : '#FF00FF',
        '--yellow-neon'  : '#FFFF00',
        '--bg-dark'      : '#1E272E',
        '--fg-dark'      : '#2D3436'
      };

      // Update a CSS variable on :root
      window.updateVar = function(varName, value) {
        document.documentElement.style.setProperty(varName, value);
      };

      // Reset all variables to defaults
      window.resetDefaults = function() {
        for (const [varName, val] of Object.entries(defaults)) {
          document.documentElement.style.setProperty(varName, val);
        }
        // Sync input values with defaults
        document.getElementById('depthRange').value = parseInt(defaults['--depth']);
        document.getElementById('depthValue').innerText = parseInt(defaults['--depth']);
        document.getElementById('glassOpacityRange').value = defaults['--glass-opacity'];
        document.getElementById('opacityValue').innerText = defaults['--glass-opacity'];

        document.getElementById('cyanColorPicker').value    = defaults['--cyan-neon'];
        document.getElementById('magentaColorPicker').value = defaults['--magenta-neon'];
        document.getElementById('yellowColorPicker').value  = defaults['--yellow-neon'];
        document.getElementById('bgDarkPicker').value       = defaults['--bg-dark'];
        document.getElementById('fgDarkPicker').value       = defaults['--fg-dark'];
      };

      // On load, initialize inputs based on current CSS variables (or defaults)
      document.addEventListener('DOMContentLoaded', function() {
        for (const [varName, val] of Object.entries(defaults)) {
          const current = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
          document.documentElement.style.setProperty(varName, current || val);
        }
        // Sync sliders/color inputs with whichever values are set in :root
        const depth = getComputedStyle(document.documentElement).getPropertyValue('--depth').replace('px','').trim();
        document.getElementById('depthRange').value = depth;
        document.getElementById('depthValue').innerText = depth;

        const opacity = getComputedStyle(document.documentElement).getPropertyValue('--glass-opacity').trim();
        document.getElementById('glassOpacityRange').value = opacity;
        document.getElementById('opacityValue').innerText = opacity;

        document.getElementById('cyanColorPicker').value    = getComputedStyle(document.documentElement).getPropertyValue('--cyan-neon').trim();
        document.getElementById('magentaColorPicker').value = getComputedStyle(document.documentElement).getPropertyValue('--magenta-neon').trim();
        document.getElementById('yellowColorPicker').value  = getComputedStyle(document.documentElement).getPropertyValue('--yellow-neon').trim();
        document.getElementById('bgDarkPicker').value       = getComputedStyle(document.documentElement).getPropertyValue('--bg-dark').trim();
        document.getElementById('fgDarkPicker').value       = getComputedStyle(document.documentElement).getPropertyValue('--fg-dark').trim();
      });
    })();
    </script>
</div>
