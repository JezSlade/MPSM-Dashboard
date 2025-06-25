<?php
// cards/BlankCard.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/card_base.php';  // loads .env, auth, API client, etc.

// Card settings from cookies (or defaults)
$cardKey             = 'BlankCard';
$cacheEnabledFlag    = isset($_COOKIE["{$cardKey}_cache_enabled"])
    ? (bool)$_COOKIE["{$cardKey}_cache_enabled"]
    : true;
$indicatorDisplayFlag = isset($_COOKIE["{$cardKey}_indicator_display"])
    ? (bool)$_COOKIE["{$cardKey}_indicator_display"]
    : true;
$ttlMinutes          = isset($_COOKIE["{$cardKey}_ttl_minutes"])
    ? max(1, (int)$_COOKIE["{$cardKey}_ttl_minutes"])
    : 5;

?>

<div
  id="<?= $cardKey ?>"
  class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600"
  data-card-key="<?= $cardKey ?>"
>
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-xl font-semibold">Blank Card Title</h2>
    <?php if ($indicatorDisplayFlag): ?>
      <span class="text-sm text-gray-400">
        <?= $cacheEnabledFlag ? "{$ttlMinutes} min cache" : 'No cache' ?>
      </span>
    <?php endif; ?>
  </header>

  <div class="card-body text-gray-200">
    No data to display yet. Start by copying this card and filling in your logic.
  </div>

  <?php if ($cacheEnabledFlag): ?>
    <footer class="mt-4 text-right text-xs text-gray-500">
      Updated <?= date('Y-m-d') ?>
    </footer>
  <?php endif; ?>
</div>
