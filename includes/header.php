Size: 3841 bytes
Last Modified: Wed Jun 11 17:09:50 EDT 2025
----------------------------------------
<?php
/**
 * includes/header.php
 *
 * Dashboard Header Partial
 *
 * Renders:
 *  - Application title (APP_NAME)
 *  - Database & API status indicators
 *  - Theme toggle
 *  - Views navigation
 *  - Customer selection dropdown (glassmorphic)
 */
?>
<header class="dashboard-header">
  <div class="header-top">
    <h1 class="app-title"><?php echo sanitize_html(APP_NAME); ?></h1>
    <div class="status-indicators">
      <span class="status-dot db-status"></span><span>Database</span>
      <span class="status-dot api-status"></span><span>API</span>
      <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme"></button>
    </div>
  </div>

  <div class="header-bottom">
    <nav class="main-navigation">
      <ul>
        <?php foreach ($available_views as $slug => $label): ?>
          <li>
            <a href="?view=<?php echo urlencode($slug); ?>"
               class="<?php echo $slug === $current_view_slug ? 'active' : ''; ?>">
              <?php echo sanitize_html($label); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="customer-selection">
      <label for="customer-select" class="sr-only">Select Customer</label>
      <div class="select-wrapper glassmorphic">
        <select id="customer-select" name="customer_code">
          <option value="">-- Select Customer --</option>
          <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $cust):
              $code = sanitize_html($cust['Code']);
              $desc = sanitize_html($cust['Description']);
              $sel  = ($code === $current_customer_id) ? 'selected' : '';
            ?>
              <option value="<?php echo $code; ?>" <?php echo $sel; ?>>
                <?php echo $desc; ?>
              </option>
            <?php endforeach; ?>
          <?php else: ?>
            <option value="">No customers available</option>
          <?php endif; ?>
        </select>
        <input
          type="text"
          id="customer-search"
          class="customer-search-input"
          placeholder="Search customer…"
          aria-label="Search customer"
        >
      </div>
    </div>
  </div>
</header>

<?php debug_log("Header rendered", 'DEBUG'); ?>
