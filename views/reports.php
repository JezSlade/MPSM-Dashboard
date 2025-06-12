<?php
/**
 * Reports & Analytics View
 *
 * This view will display various reports and analytics data.
 * The content will be dynamically loaded based on user selections.
 */
// Access data passed from render_view via $_data
$available_views = $_data['available_views'] ?? [];
$current_view_slug = $_data['current_view_slug'] ?? 'reports';
$selected_customer_id = $_data['selected_customer_id'] ?? null;

$cid = $selected_customer_id ?? null;
debug_log("Loading Reports view. Customer: ".($cid??'None'), 'INFO');
?>
<h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug]); ?></h2>
<div class="cards-grid">
  </div>