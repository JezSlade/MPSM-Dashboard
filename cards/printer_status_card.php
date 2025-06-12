<?php
/**
 * Printer Status Card Component
 *
 * This card displays an overview of printer statuses.
 *
 * @param array $_data Associative array containing:
 * - 'customer_id': The ID of the currently selected customer.
 * - 'card_title': Title for the card.
 * - 'status_summary': An associative array of printer statuses (online, offline, warning, error, unknown) and their counts.
 */
// Access data passed from render_view via $_data
$customer_id = $_data['customer_id'] ?? null;
$card_title = $_data['card_title'] ?? 'Printer Status';
$status_summary = $_data['status_summary'] ?? [
    'online' => 0, 'offline' => 0, 'warning' => 0, 'error' => 0, 'unknown' => 0
];
?>

<div class="card printer-status-card">
  <h3><?php echo sanitize_html($card_title); ?></h3>
  <div class="card-content">
    <ul>
      <li class="status-online">Online: <strong><?php echo sanitize_html($status_summary['online']); ?></strong></li>
      <li class="status-offline">Offline: <strong><?php echo sanitize_html($status_summary['offline']); ?></strong></li>
      <li class="status-warning">Warning: <strong><?php echo sanitize_html($status_summary['warning']); ?></strong></li>
      <li class="status-error">Error: <strong><?php echo sanitize_html($status_summary['error']); ?></strong></li>
      <li class="status-unknown">Unknown: <strong><?php echo sanitize_html($status_summary['unknown']); ?></strong></li>
    </ul>
  </div>
  <div class="card-actions">
    <a href="#" class="small-button view-details-button">View Details</a>
  </div>
</div>