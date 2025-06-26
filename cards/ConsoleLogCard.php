<?php
/**
 * cards/ConsoleLogCard.php — Display a console log within a card.
 */
?>
<div class="neumorphic p-4">
<?php
$title = 'Console Log';
$cardId = 'ConsoleLogCard';
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
    
    // Check if the content div actually exists for this specific card
    // This prevents errors if the script runs before the element is ready
    if (!content) {
        console.warn('In-card log content div not found. Skipping in-card logging setup.');
        return;
    }

    // Store original console functions
    const originalLog = console.log;
    const originalError = console.error;

    function addEntry(type, args) {
      const msg = document.createElement('div');
      msg.textContent = `[${type}] ${args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' ')}`;
      content.appendChild(msg);
      // Keep scroll at the bottom
      content.scrollTop = content.scrollHeight;
    }

    // Override console functions for this card's display
    console.log = (...args) => {
        originalLog.apply(console, args); // Call original console.log
        addEntry('LOG', args);
    };
    console.error = (...args) => {
        originalError.apply(console, args); // Call original console.error
        addEntry('ERROR', args);
    };

    // Log a test message to confirm it's working
    console.log('In-card logging activated!');
    console.error('Test error message within card.');
  });
</script>