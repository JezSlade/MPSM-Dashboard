<?php
/**
 * includes/card_header.php — Shared header for cards
 * Expects:
 *   $title (string),
 *   $cardId (string),
 *   $allowMinimize (bool),
 *   $allowSettings (bool),
 *   $allowClose (bool)
 */
?>
<header class="card-header flex items-center justify-between p-2 rounded-t-lg bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 shadow-md">
  <h2 class="text-base font-semibold tracking-wide truncate" title="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>">
    <?php echo htmlspecialchars($title, ENT_QUOTES); ?>
  </h2>
  <div class="flex items-center gap-1">
    <?php if (!empty($allowMinimize)): ?>
      <button class="neu-btn" data-action="minimize" data-card="<?php echo $cardId; ?>" aria-label="Minimize">
        <i data-feather="chevron-down"></i>
      </button>
    <?php endif; ?>
    <?php if (!empty($allowSettings)): ?>
      <button class="neu-btn" data-action="settings" data-card="<?php echo $cardId; ?>" aria-label="Settings">
        <i data-feather="settings"></i>
      </button>
    <?php endif; ?>
    <?php if (!empty($allowClose)): ?>
      <button class="neu-btn" data-action="close" data-card="<?php echo $cardId; ?>" aria-label="Close">
        <i data-feather="x"></i>
      </button>
    <?php endif; ?>
  </div>
</header>
