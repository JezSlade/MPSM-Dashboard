<?php
/**
 * includes/header.php
 *
 * MPSM Dashboard – Header Partial
 * Renders:
 *  - Application branding
 *  - DB & API status indicators
 *  - Theme toggle
 *  - Navigation links (Views)
 *  - Customer dropdown + search
 */

global $debug_log_entries;

$db_status           = $db_status ?? ['status'=>'unknown','message'=>'Status not retrieved.'];
$api_status          = $api_status ?? ['status'=>'unknown','message'=>'Status not retrieved.'];
$customers           = $customers ?? [];
$current_customer_id = $current_customer_id ?? null;
$available_views     = $available_views ?? [];
$current_view_slug   = $current_view_slug ?? 'dashboard';

debug_log("Rendering header.php. DB={$db_status['status']}, API={$api_status['status']}", 'DEBUG');
debug_log("Views: " . implode(', ', array_keys($available_views)), 'DEBUG');
debug_log("Current view slug: {$current_view_slug}", 'DEBUG');
?>
<header class="dashboard-header glassmorphic">
  <div class="header-top">
    <div class="app-branding">
      <h1><?php echo sanitize_html(APP_NAME); ?></h1>
    </div>
    <div class="status-indicators">
      <div class="status-item db-status">
        <span class="status-dot status-<?php echo sanitize_html($db_status['status']); ?>"
              title="Database: <?php echo sanitize_html($db_status['message']); ?>"></span>
        <span>Database</span>
      </div>
      <div class="status-item api-status">
        <span class="status-dot status-<?php echo sanitize_html($api_status['status']); ?>"
              title="API: <?php echo sanitize_html($api_status['message']); ?>"></span>
        <span>API</span>
      </div>
      <button id="theme-toggle" class="theme-toggle" title="Toggle Theme">
        <span class="icon-light">☀️</span>
        <span class="icon-dark">🌙</span>
      </button>
      <?php debug_log("Header status & theme toggle rendered.", 'DEBUG'); ?>
    </div>
  </div>

  <div class="header-bottom">
    <nav class="main-navigation">
      <ul>
        <?php foreach ($available_views as $slug => $label): 
          $active = ($slug === $current_view_slug) ? 'active' : '';
          $url    = BASE_URL . '?view=' . sanitize_url($slug);
          debug_log("Nav link: {$slug} (active={$active})", 'DEBUG');
        ?>
          <li>
            <a href="<?php echo sanitize_html($url); ?>" class="<?php echo sanitize_html($active); ?>">
              <?php echo sanitize_html($label); ?>
            </a>
          </li>
        <?php endforeach; ?>
        <?php if (empty($available_views)): ?>
          <?php debug_log("No views available for nav.", 'WARNING'); ?>
          <li><span>No views available</span></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div class="customer-selection">
      <label for="customer-select" class="sr-only">Select Customer</label>
      <div class="select-wrapper">
        <select id="customer-select" name="customer_code">
          <option value="">-- Select Customer --</option>
          <?php if (!empty($customers)): ?>
            <?php
              debug_log("Populating customers (".count($customers).")", 'INFO');
              foreach ($customers as $cust):
                $code = sanitize_html($cust['Code']);
                $desc = sanitize_html($cust['Description']);
                $sel  = ($code === $current_customer_id) ? 'selected' : '';
                debug_log("Added to dropdown: Code={$code}, Description='{$desc}'", 'DEBUG');
            ?>
              <option value="<?php echo $code; ?>" <?php echo $sel; ?>>
                <?php echo $desc; ?>
              </option>
            <?php endforeach; ?>
          <?php else: ?>
            <?php debug_log("Customer list empty.", 'WARNING'); ?>
            <option disabled>No customers available</option>
          <?php endif; ?>
        </select>
        <input
          type="text"
          id="customer-search"
          placeholder="Search customer…"
          aria-label="Search customer"
        >
      </div>
      <button id="apply-customer-filter" class="cta-button">Apply Filter</button>
      <?php debug_log("Apply filter button rendered.", 'DEBUG'); ?>
    </div>
  </div>
</header>
<?php debug_log("Header.php complete.", 'INFO'); ?>
