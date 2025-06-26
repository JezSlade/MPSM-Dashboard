<?php
/**
 * cards/CardAppLog.php — FIXED VERSION
 * Removed duplicate header and conflicting JavaScript
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file
?>
<div class="neumorphic p-4">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">Application Log</h2>
    </header>
  <div id="appLogContent" class="h-64 overflow-auto bg-gray-100 dark:bg-gray-800 p-2 text-xs font-mono">
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('appLogContent');
    const originalLog = console.log, originalError = console.error;

    function addEntry(type, args) {
      const msg = document.createElement('div');
      msg.textContent = `[${type}] ${args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' ')}`;
      content.prepend(msg); // Changed to prepend to add new logs at the top
      // content.scrollTop = content.scrollHeight; // REMOVED: No longer needed as new logs are at top
    }

    console.log   = (...args) => { originalLog.apply(console, args); addEntry('LOG', args); };
    console.error = (...args) => { originalError.apply(console, args); addEntry('ERROR', args); };
  });
</script>