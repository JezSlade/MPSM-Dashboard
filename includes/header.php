<?php
/**
 * includes/header.php
 *
 * Renders:
 * - App branding
 * - DB & API status dots
 * - Theme toggle
 * - Views navigation
 * - Customer dropdown
 */

$db_status           = $db_status ?? ['status'=>'unknown','message'=>'Status not retrieved.'];
$api_status          = $api_status ?? ['status'=>'unknown','message'=>'Status not retrieved.'];
$customers           = $customers ?? [];
$current_customer_id = $current_customer_id ?? null;
$available_views     = $available_views ?? [];
$current_view_slug   = $current_view_slug ?? 'dashboard';

debug_log("Rendering header", 'DEBUG');
?>
<header class="dashboard-header glassmorphic">
  <div class="header-top">
    <div class="app-branding"><h1><?php echo sanitize_html(APP_NAME); ?></h1></div>
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
    </div>
  </div>

  <div class="header-bottom">
    <nav class="main-navigation">
      <ul>
        <?php foreach ($available_views as $slug => $label): 
          $active = $slug=== $current_view_slug ? 'active' : '';
          $url    = BASE_URL . '?view=' . sanitize_url($slug);
        ?>
          <li><a href="<?php echo sanitize_html($url); ?>" class="<?php echo sanitize_html($active); ?>">
            <?php echo sanitize_html($label); ?>
          </a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="customer-selection">
      <label for="customer-select" class="sr-only">Select Customer</label>
      <div class="select-wrapper">
        <select id="customer-select" name="customer_code">
          <option value="">-- Select Customer --</option>
          <?php if($customers): foreach($customers as $c):
            $code = sanitize_html($c['Code']);
            $desc = sanitize_html($c['Description']);
            $sel  = $code===$current_customer_id?'selected':'';
          ?>
            <option value="<?php echo $code;?>" <?php echo $sel;?>><?php echo $desc;?></option>
          <?php endforeach; else: ?>
            <option disabled>No customers available</option>
          <?php endif; ?>
        </select>
        <input type="text" id="customer-search" placeholder="Search customer…">
      </div>
      <button id="apply-customer-filter" class="cta-button">Apply Filter</button>
    </div>
  </div>
</header>
<?php debug_log("Header complete", 'DEBUG'); ?>
