<?php
/**
 * views/analytics.php
 *
 * View: Analytics
 * Expects:
 *   - $selected_customer_id
 */
$cid = $selected_customer_id ?? null;
debug_log("Loading Analytics view. Customer: ".($cid??'None'), 'INFO');
?>
<h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug]); ?></h2>
<div class="cards-grid">
  <!-- Add analytics cards here -->
</div>
