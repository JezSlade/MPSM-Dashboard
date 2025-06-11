<?php
/**
 * views/reports.php
 *
 * View: Reports
 * Expects:
 *   - $selected_customer_id
 */
$cid = $selected_customer_id ?? null;
debug_log("Loading Reports view. Customer: ".($cid??'None'), 'INFO');
?>
<h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug]); ?></h2>
<div class="cards-grid">
  <!-- Add report cards here -->
</div>
