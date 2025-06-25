<?php
/**
 * cards/CardAppLog.php — Extra-large running log of app events
 *
 * Changelog:
 * - New card to capture and display console.log and console.error entries in real time.
 * - Spans two grid columns via inline style.
 * - Uses a <div> with id="appLogContent" for log entries, styled scrollable.
 */
?>
<div id="appLogCard" class="neumorphic p-4" style="grid-column: span 2;">
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
    const originalLog = console.log;
    const originalError = console.error;

    function addEntry(type, args) {
      const msg = document.createElement('div');
      msg.textContent = `[${type}] ${args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' ')}`;
      content.appendChild(msg);
      content.scrollTop = content.scrollHeight;
    }

    console.log = function(...args) {
      originalLog.apply(console, args);
      addEntry('LOG', args);
    };
    console.error = function(...args) {
      originalError.apply(console, args);
      addEntry('ERROR', args);
    };

    // Minimize toggle
    const minimizeBtn = document.getElementById('appLogMinimize');
    const card = document.getElementById('appLogCard');
    minimizeBtn.addEventListener('click', () => {
      if (card.style.maxHeight && card.style.maxHeight !== '') {
        card.style.maxHeight = '';
        card.style.overflow = '';
        minimizeBtn.querySelector('i').setAttribute('data-feather','minus');
      } else {
        card.style.maxHeight = '2.5rem';
        card.style.overflow = 'hidden';
        minimizeBtn.querySelector('i').setAttribute('data-feather','plus');
      }
      feather.replace();
    });
  });
</script>
