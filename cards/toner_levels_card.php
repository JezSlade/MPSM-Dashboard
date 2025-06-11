<?php
/**
 * cards/toner_levels_card.php
 *
 * Card: Toner Levels
 * Expects:
 *   - $customer_id
 *   - $card_title
 *   - $toner_data (array: black, cyan, magenta, yellow)
 *   - $low_threshold (int)
 */
$cid   = $customer_id   ?? 'N/A';
$title = $card_title    ?? 'Toner Levels';
$data  = $toner_data    ?? ['black'=>0,'cyan'=>0,'magenta'=>0,'yellow'=>0];
$th    = isset($low_threshold) ? (int)$low_threshold : 20;
debug_log("Rendering Toner Levels Card for {$cid}", 'DEBUG');
?>
<div class="card toner-levels-card">
  <h3><?php echo sanitize_html($title); ?></h3>
  <?php if ($cid!=='N/A'): ?>
    <p class="card-subtitle">Customer: <?php echo sanitize_html($cid); ?></p>
  <?php endif; ?>
  <ul class="toner-list">
    <?php foreach ($data as $color => $level):
      $warn = $level < $th ? 'low' : '';
    ?>
      <li class="toner-item <?php echo sanitize_html($warn); ?>">
        <span class="toner-color"><?php echo ucfirst(sanitize_html($color)); ?>:</span>
        <span class="toner-level"><?php echo sanitize_html((string)$level); ?>%</span>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if (min($data) < $th): ?>
    <p class="toner-warning">⚠️ Some toners below <?php echo sanitize_html((string)$th); ?>%</p>
  <?php endif; ?>
</div>
