<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
?>

<section>
  <?php if (!$customerCode): ?>
    <p>Please select a customer from the menu to view their devices.</p>
  <?php else: ?>
    <?php
      $_GET['customer'] = $customerCode;
      include __DIR__ . '/../cards/printer_card.php';
    ?>
  <?php endif; ?>
</section>

