<?php
/**
 * includes/card_header.php — Shared header for cards
 * Expects:
 *   $title (string),
 *   $cardId (string),
 *   $allowMinimize (bool),
 *   $allowSettings (bool)
 */
?>
<header class="card-header flex items-center justify-between p-2">
  <h2 class="font-medium text-lg"><?php echo htmlspecialchars($title, ENT_QUOTES); ?></h2>
  <div class="flex space-x-1">
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
  </div>
</header>

<!--
Changelog:
- Created shared card_header.php for consistent card headers.
-->