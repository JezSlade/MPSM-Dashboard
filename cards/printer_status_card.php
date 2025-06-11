<?php
/**
 * cards/printer_status_card.php
 *
 * Card: Printer Status
 * Expects:
 *   - $customer_id
 *   - $card_title
 *   - $status_summary (array: online, offline, warning, error, unknown)
 */
$cid   = $customer_id    ?? 'N/A';
$title = $card_title     ?? 'Printer Status';
$sum   = $status_summary ?? ['online'=>0,'offline'=>0,'warning'=>0,'error'=>0,'unknown'=>0];
debug_log("Rendering Printer Status Card for {$cid}", 'DEBUG');
?>
<div class="card printer-status-card">
  <h3><?php echo sanitize_html($title); ?></h3>
  <?php if ($cid!=='N/A'): ?>
    <p class="card-subtitle">Customer: <?php echo sanitize_html($cid); ?></p>
  <?php endif; ?>
  <div class="status-summary-grid">
    <?php foreach ($sum as $k => $v): ?>
      <div class="status-item <?php echo sanitize_html($k); ?>">
        <span class="count"><?php echo sanitize_html((string)$v); ?></span>
        <span class="label"><?php echo ucfirst(sanitize_html($k)); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="total-printers">
    Total: <strong><?php echo sanitize_html((string)array_sum($sum)); ?></strong>
  </p>
  <div class="card-actions">
    <a href="#" class="small-button view-details-button">View Details</a>
  </div>
</div>
