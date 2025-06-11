<?php
/**
 * MPSM Dashboard - Header Partial
 *
 * Renders:
 *  - App branding
 *  - DB & API status indicators
 *  - Theme toggle
 *  - Navigation links
 *  - Customer dropdown + search
 *
 * This version has the floating debug panel removed in favor of the footer one.
 */

// Bring in any debug entries collected so far
global $debug_log_entries;

// Default guards in case variables weren’t passed
$db_status           = $db_status ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$api_status          = $api_status ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$customers           = $customers ?? [];
$current_customer_id = $current_customer_id ?? null;
$available_views     = $available_views ?? [];
$current_view_slug   = $current_view_slug ?? 'dashboard';

debug_log("Rendering header.php. DB Status: " . $db_status['status'] . ", API Status: " . $api_status['status'], 'DEBUG');
debug_log("Available views: " . implode(', ', array_keys($available_views)), 'DEBUG');
debug_log("Current view slug: " . sanitize_html($current_view_slug), 'DEBUG');
?>
<header class="dashboard-header">
  <div class="header-top">
    <div class="app-branding">
      <h1><?php echo sanitize_html(APP_NAME); ?></h1>
    </div>
    <div class="status-indicators">
      <div class="status-item db-status">
        <span class="status-dot status-<?php echo sanitize_html($db_status['status']); ?>"
              title="Database Status: <?php echo sanitize_html($db_status['message']); ?>"></span>
        <span>Database</span>
      </div>
      <div class="status-item api-status">
        <span class="status-dot status-<?php echo sanitize_html($api_status['status']); ?>"
              title="API Status: <?php echo sanitize_html($api_status['message']); ?>"></span>
        <span>API</span>
      </div>
      <button id="theme-toggle" class="theme-toggle" title="Toggle Dark/Light Theme">
        <span class="icon-light">☀️</span>
        <span class="icon-dark">🌙</span>
      </button>
      <?php debug_log("Status indicators and theme toggle rendered.", 'DEBUG'); ?>
    </div>
  </div>

  <div class="header-bottom">
    <nav class="main-navigation">
      <ul>
        <?php
        foreach ($available_views as $slug => $display_name) {
            $is_active = ($slug === $current_view_slug) ? 'active' : '';
            $nav_link  = BASE_URL . '?view=' . sanitize_url($slug);
            debug_log("Rendering navigation link for view: $slug (Active: $is_active)", 'DEBUG');
            echo '<li><a href="' . sanitize_html($nav_link) . '" class="' . sanitize_html($is_active) . '">' .
                  sanitize_html($display_name) .
                 '</a></li>';
        }
        if (empty($available_views)) {
            debug_log("No views found to render in navigation. Check 'views/' directory.", 'WARNING');
            echo '<li><span>No views available</span></li>';
        }
        ?>
      </ul>
    </nav>

    <div class="customer-selection">
      <label for="customer-select" class="sr-only">Select Customer:</label>
      <div class="select-wrapper">
        <select id="customer-select" name="customer_id">
          <option value="">-- Select Customer --</option>
          <?php
          if (!empty($customers)) {
              debug_log("Populating customer dropdown with " . count($customers) . " customers.", 'INFO');
              foreach ($customers as $cust) {
                  $cid      = sanitize_int($cust['id']);
                  $cname    = sanitize_html($cust['name']);
                  $selected = ($cid === $current_customer_id) ? 'selected' : '';
                  echo "<option value=\"{$cid}\" {$selected}>{$cname}</option>";
                  debug_log("Added customer: ID {$cid}, Name '{$cname}'.", 'DEBUG');
              }
          } else {
              debug_log("No customer data available to populate dropdown.", 'WARNING');
              echo '<option disabled>No customers available</option>';
          }
          ?>
        </select>

        <input
          type="text"
          id="customer-search"
          placeholder="Search customer…"
          aria-label="Search customer"
        >
        <?php debug_log("Customer dropdown and search input rendered.", 'DEBUG'); ?>
      </div>
      <button id="apply-customer-filter" class="cta-button">Apply Filter</button>
      <?php debug_log("Apply customer filter button rendered.", 'DEBUG'); ?>
    </div>
  </div>
</header>

<?php debug_log("Header.php rendering complete.", 'INFO'); ?>
