<?php
/**
 * includes/header.php — Header with global controls and card-settings trigger
 *
 * Changelog:
 * - Changed root element class from `neu-btn` to `neumorphic` for proper styling.
 * - Updated title to use APP_NAME constant instead of DEALER_CODE.
 * - Verified all button IDs (theme-toggle, refresh-all, clear-session, view-error-log, card-settings).
 */
?>
<header class="flex items-center justify-between p-4 neumorphic">
  <h1 class="text-xl font-semibold">
    <?php echo htmlspecialchars(defined('APP_NAME') ? APP_NAME : (getenv('APP_NAME') ?: 'MPS Monitor Dashboard'), ENT_QUOTES, 'UTF-8'); ?>
  </h1>
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
    <button id="view-error-log" aria-label="Toggle application log" class="neu-btn">
      <i data-feather="file-text"></i>
    </button>
    <button id="card-settings" aria-label="Card settings" class="neu-btn">
      <i data-feather="settings"></i>
    </button>
  </div>
