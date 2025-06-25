<?php
/**
 * header.php — Header with settings button
 */
?>
<header class="flex items-center justify-between p-4 neu-btn">
  <h1 class="text-xl font-semibold">Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?></h1>
  <div class="flex items-center space-x-3">
    <button id="theme-toggle" aria-label="Toggle theme" class="neu-btn"><i data-feather="sun"></i></button>
    <button id="refresh-all" aria-label="Hard refresh" class="neu-btn"><i data-feather="refresh-cw"></i></button>
    <button id="clear-session" aria-label="Clear session" class="neu-btn"><i data-feather="trash-2"></i></button>
    <button id="view-error-log" aria-label="View debug log" class="neu-btn"><i data-feather="file-text"></i></button>
    <button id="card-settings" aria-label="Card settings" class="neu-btn"><i data-feather="settings"></i></button>
  </div>
</header>
