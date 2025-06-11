<?php
/**
 * cards/toner_levels_card.php
 *
 * Card: Toner Levels
 * Expects:
 *  - $customer_id  (string|null)
 *  - $card_title   (string)
 *  - $toner_data   (array: black, cyan, magenta, yellow)
 *  - $low_threshold (int)
 */
$customer_id  = $customer_id  ?? 'N/A';
$card_title   = $card_title   ?? 'Toner Levels';
$toner_data   = $toner_data   ?? ['black'=>0,'cyan'=>0,'magenta'=>0,'yellow'=>0];
$low_threshold= isset($low_threshold) ? (int)$low_threshold : 20;
debug_log("Rendering Toner Levels Card for {$customer_id}", 'DEBUG');
?>
<div class="card toner-levels-card">
  <h3><?php echo sanitize_html($card_title); ?></h3>
  <?php if($customer_id!=='N/A'): ?>
    <p class="card-subtitle">Customer: <?php echo sanitize_html($customer_id); ?></p>
  <?php endif; ?>
  <ul class="toner-list">
    <?php foreach($toner_data as $color=>$level): 
      $warn = $level < $low_threshold ? 'low' : '';
    ?>
      <li class="toner-item <?php echo sanitize_html($warn); ?>">
        <span class="toner-color"><?php echo ucfirst(sanitize_html($color)); ?>:</span>
        <span class="toner-level"><?php echo sanitize_html((string)$level); ?>%</span>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if(min($toner_data) < $low_threshold): ?>
    <p class="toner-warning">⚠️ Some toners below <?php echo sanitize_html((string)$low_threshold); ?>%</p>
  <?php endif; ?>
</div>
