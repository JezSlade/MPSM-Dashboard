<?php
/**
 * header.php — Header component for the dashboard.
 * Contains global controls and page title.
 */
?>
<header class="flex items-center justify-between p-4 neu-btn">
  <!-- Page Title -->
  <h1 class="text-xl font-semibold">
    Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?>
  </h1>
  <!-- Global Controls -->
  <div class="flex items-center space-x-3">
    <button id="theme-toggle" aria-label="Toggle theme" class="neu-btn">
      <i data-feather="sun"></i>
    </button>
    <button id="refresh-all" aria-label="Hard refresh" class="neu-btn">
      <i data-feather="refresh-cw"></i>
    </button>
    <button id="clear-session" aria-label="Clear session" class="neu-btn">
      <i data-feather="trash-2"></i>
    </button>
    <button id="view-error-log" aria-label="View debug log" class="neu-btn">
      <i data-feather="file-text"></i>
    </button>
  </div>
</header>
