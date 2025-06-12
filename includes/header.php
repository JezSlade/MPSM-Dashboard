<?php
/**
 * includes/header.php
 *
 * Dashboard Header Partial
 *
 * Renders:
 *  - App title
 *  - Status indicators (DB/API)
 *  - Theme toggle
 *  - View navigation tabs
 *  - Customer-select form with instant GET submit
 */
?>
<header class="dashboard-header">
  <div class="header-top">
    <h1 class="app-title"><?php echo sanitize_html(APP_NAME); ?></h1>
    <div class="status-indicators">
      <span class="status-dot <?php echo $db_status['status']==='ok'?'status-ok':'status-error'; ?>"></span>
      <span>Database</span>
      <span class="status-dot <?php echo $api_status['status']==='ok'?'status-ok':'status-error'; ?>"></span>
      <span>API</span>
      <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
        <span class="icon-dark">🌙</span>
        <span class="icon-light">☀️</span>
      </button>
    </div>
  </div>

  <div class="header-bottom">
    <nav class="main-navigation">
      <ul>
        <?php foreach ($available_views as $slug => $label): ?>
          <li>
            <a
              href="?view=<?php echo urlencode($slug); ?>"
              class="<?php echo $slug === $current_view_slug ? 'active' : ''; ?>"
            >
              <?php echo sanitize_html($label); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="customer-selection">
      <form
        id="customer-form"
        action="?view=<?php echo urlencode($current_view_slug); ?>"
        method="GET"
        class="select-wrapper glassmorphic"
      >
        <input type="hidden" name="view" value="<?php echo sanitize_html($current_view_slug); ?>">
        <label for="customer-search" class="sr-only">Search Customer</label>
        <input
          type="search"
          id="customer-search"
          name="customer_search"
          class="customer-search-input"
          placeholder="Search…"
          autocomplete="off"
        >
        <label for="customer-select" class="sr-only">Select Customer</label>
        <select
          id="customer-select"
          name="customer_code"
          onchange="this.form.submit()"
        >
          <option value="">-- Select Customer --</option>
          <?php foreach ($customers as $cust):
            $code = sanitize_html($cust['Code']);
            $desc = sanitize_html($cust['Description']);
            $sel  = ($code === $current_customer_id) ? 'selected' : '';
          ?>
            <option value="<?php echo $code; ?>" <?php echo $sel; ?>>
              <?php echo $desc; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
</header>

<?php debug_log("Header rendered", 'DEBUG'); ?>
