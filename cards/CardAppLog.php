<?php
/**
 * cards/CardAppLog.php — Extra-large running log of app events
 *
 * Changelog:
 * - Guarded icon swap in minimize toggle with null-check to avoid `btn.querySelector(...) is null`.
 * - Ensured `appLogCard` is hidden by default via inline style.
 */
?>
<div id="appLogCard" class="neumorphic p-4" style="display:none; grid-column: span 2;">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Application Log</h2>
    <button class="neu-btn" aria-label="Minimize log card" id="appLogMinimize">
      <i data-feather="minus"></i>
    </button>
  </header>
  <div id="appLogContent" class="h-64 overflow-auto bg-gray-100 dark:bg-gray-800 p-2 text-xs font-mono">
    <!-- Log entries will appear here -->
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('appLogContent');
    const originalLog = console.log, originalError = console.error;

    function addEntry(type, args) {
      const msg = document.createElement('div');
      msg.textContent = `[${type}] ${args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' ')}`;
      content.appendChild(msg);
      content.scrollTop = content.scrollHeight;
    }

    console.log   = (...args) => { originalLog.apply(console, args); addEntry('LOG', args); };
    console.error = (...args) => { originalError.apply(console, args); addEntry('ERROR', args); };

    // Minimize toggle
    const btn = document.getElementById('appLogMinimize');
    const card = document.getElementById('appLogCard');
    btn.addEventListener('click', () => {
      const isMinimized = card.style.maxHeight;
      if (isMinimized) {
        card.style.maxHeight = '';
        card.style.overflow = '';
      } else {
        card.style.maxHeight = '2.5rem';
        card.style.overflow = 'hidden';
      }
      const icon = btn.querySelector('i');
      if (icon) {
        icon.setAttribute('data-feather', isMinimized ? 'minus' : 'plus');
      }
      feather.replace();
    });
  });
</script>
