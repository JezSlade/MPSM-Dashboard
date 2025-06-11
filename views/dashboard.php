<?php
/**
 * views/dashboard.php
 *
 * View: Dashboard Overview
 * Expects:
 *  - $selected_customer_id (string|null)
 */
$selected_customer_id = $selected_customer_id ?? null;
debug_log("Loading Dashboard view. Customer: " . ($selected_customer_id ?? 'None'), 'INFO');
?>
<h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug]); ?></h2>
<div class="cards-grid">
  <?php
    // Example cards
    render_card('printer_status_card', [
      'customer_id'=>$selected_customer_id,
      'card_title'=>'Printer Status Overview',
      'status_summary'=>['online'=>10,'offline'=>2,'warning'=>1,'error'=>0,'unknown'=>0]
    ]);
    render_card('toner_levels_card', [
      'customer_id'=>$selected_customer_id,
      'card_title'=>'Toner Levels',
      'toner_data'=>['black'=>80,'cyan'=>70,'magenta'=>65,'yellow'=>50],
      'low_threshold'=>30
    ]);
  ?>
</div>
