<?php
/**
 * Toner Levels Card Component
 *
 * This card displays an overview of toner levels for a customer's printers.
 *
 * @param array $_data Associative array containing:
 * - 'customer_id': The ID of the currently selected customer.
 * - 'card_title': Title for the card.
 * - 'toner_data': An associative array of toner colors (black, cyan, magenta, yellow) and their percentage levels.
 * - 'low_threshold': The percentage threshold below which toner is considered low.
 */
// Access data passed from render_view via $_data
$customer_id = $_data['customer_id'] ?? null;
$card_title = $_data['card_title'] ?? 'Toner Levels';
$toner_data = $_data['toner_data'] ?? [
    'black' => 0, 'cyan' => 0, 'magenta' => 0, 'yellow' => 0
];
$low_threshold = $_data['low_threshold'] ?? 20; // Default low threshold
?>

<div class="card toner-levels-card">
  <h3><?php echo sanitize_html($card_title); ?></h3>
  <div class="card-content">
    <ul>
      <?php foreach ($toner_data as $color => $level):
        $status_class = ($level <= $low_threshold) ? 'toner-low' : 'toner-ok';
      ?>
        <li class="<?= sanitize_html($status_class) ?>">
          <?php echo sanitize_html(ucfirst($color)); ?>: <strong><?php echo sanitize_html($level); ?>%</strong>
          <?php if ($level <= $low_threshold): ?>
            <span class="warning-icon">&#9888; Low!</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card-actions">
    <a href="#" class="small-button view-details-button">View Details</a>
  </div>
</div>