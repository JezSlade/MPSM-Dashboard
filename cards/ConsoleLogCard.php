<?php
/**
 * cards/ConsoleLogCard.php — Display a console log within a card.
 */
include __DIR__ . '/../includes/card_helpers.php'; // NEW: Include helper file
?>
<div class="neumorphic p-4">
<?php
$allowMinimize = true;
$allowSettings = true;
include __DIR__ . '/../includes/card_header.php';
?>
 <div class="neumorphic p-4">
  <header class="flex items-center justify-between mb-2">
    <h2 class="font-medium text-lg">In-Card Console Log</h2>
  </header>
  <div id="inCardLogContent" class="h-64 overflow-auto bg-gray-100 dark:bg-gray-800 p-2 text-xs font-mono">
    Console initialized for this card.
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('inCardLogContent');
    
    if (!content) {
        console.warn('In-card log content div not found. Skipping in-card logging setup.');
        return;
    }

    const originalLog = console.log;
    const originalError = console.error;

    function addEntry(type, args) {
      const msg = document.createElement('div');
      msg.textContent = `[${type}] ${args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' ')}`;
      content.prepend(msg); // Changed to prepend to add new logs at the top
      // content.scrollTop = content.scrollHeight; // REMOVED: No longer needed as new logs are at top
    }

    console.log = (...args) => {
        originalLog.apply(console, args);
        addEntry('LOG', args);
    };
    console.error = (...args) => {
        originalError.apply(console, args);
        addEntry('ERROR', args);
    };

    // Log a test message to ensure the in-card console is working
    console.log('In-card console operational.');
    console.error('Test error message within card.');
  });
</script>