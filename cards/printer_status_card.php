<?php
/**
 * cards/printer_status_card.php
 *
 * Card: Printer Status
 * Expects:
 *  - $customer_id       (string|null)
 *  - $card_title        (string)
 *  - $status_summary    (array with keys: online, offline, warning, error[, unknown])
 */
$customer_id    = $customer_id    ?? 'N/A';
$card_title     = $card_title     ?? 'Printer Status';
$status_summary = $status_summary ?? ['online'=>0,'offline'=>0,'warning'=>0,'error'=>0,'unknown'=>0];
debug_log("Rendering Printer Status Card for {$customer_id}", 'DEBUG');
?>
<div class="card printer-status-card">
  <h3><?php echo sanitize_html($card_title); ?></h3>
  <?php if($customer_id!=='N/A'): ?>
    <p class="card-subtitle">Customer: <?php echo sanitize_html($customer_id); ?></p>
  <?php endif; ?>
  <div class="status-summary-grid">
    <?php foreach($status_summary as $key=>$count): ?>
      <div class="status-item <?php echo sanitize_html($key); ?>">
        <span class="count"><?php echo sanitize_html((string)$count); ?></span>
        <span class="label"><?php echo ucfirst(sanitize_html($key)); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="total-printers">
    Total: <strong><?php echo sanitize_html((string)array_sum($status_summary)); ?></strong>
  </p>
  <div class="card-actions">
    <a href="#" class="small-button view-details-button">View Details</a>
  </div>
</div>
