<?php
/**
 * Dashboard Overview View
 */
$cid = $selected_customer_id ?? null;
debug_log("Loading Dashboard view. Customer: ".($cid??'None'), 'INFO');
?>
<h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug]); ?></h2>
<div class="cards-grid">
  <?php
    render_card('printer_status_card', [
      'customer_id'=>$cid,
      'card_title'=>'Printer Status Overview',
      'status_summary'=>['online'=>10,'offline'=>2,'warning'=>1,'error'=>0,'unknown'=>0]
    ]);
    render_card('toner_levels_card', [
      'customer_id'=>$cid,
      'card_title'=>'Toner Levels',
      'toner_data'=>['black'=>80,'cyan'=>70,'magenta'=>65,'yellow'=>50],
      'low_threshold'=>30
    ]);
  ?>
</div>
