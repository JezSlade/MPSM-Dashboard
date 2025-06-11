<?php
/**
 * MPSM Dashboard - Header Partial
 *
 * This file contains the HTML structure and PHP logic for the dashboard's header section.
 * It includes key UI elements such as the application title, database and API status indicators,
 * a searchable customer dropdown, and navigation links for different views.
 *
 * This file expects the following variables to be available (passed via include_partial):
 * - $db_status (array): Database connection status.
 * - $api_status (array): MPS Monitor API connection status.
 * - $customers (array): List of available customers for the dropdown.
 * - $current_customer_id (int|null): The ID of the currently selected customer.
 * - $available_views (array): List of all discoverable views.
 * - $current_view_slug (string): The slug of the currently active view.
 *
 * Debugging Philosophy:
 * Log the status of each component (DB, API, Customer dropdown) during rendering
 * to identify issues with data availability or rendering early.
 */

// Ensure variables are defined, providing defaults to prevent errors if not passed.
$db_status         = $db_status ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$api_status        = $api_status ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$customers         = $customers ?? [];
$current_customer_id = $current_customer_id ?? null;
$available_views   = $available_views ?? [];
$current_view_slug = $current_view_slug ?? 'dashboard';

debug_log("Rendering header.php. DB Status: " . $db_status['status'] . ", API Status: " . $api_status['status'], 'DEBUG');
debug_log("Available views: " . implode(', ', array_keys($available_views)), 'DEBUG');
debug_log("Current view slug: " . sanitize_html($current_view_slug), 'DEBUG');

?>
<header class="dashboard-header glassmorphic">
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
                // Render navigation links for each discoverable view.
                foreach ($available_views as $slug => $display_name) {
                    $is_active = ($slug === $current_view_slug) ? 'active' : '';
                    $nav_link_url = BASE_URL . '?view=' . sanitize_url($slug);
                    debug_log("Rendering navigation link for view: $slug (Active: $is_active)", 'DEBUG');
                    echo '<li><a href="' . sanitize_html($nav_link_url) . '" class="' . sanitize_html($is_active) . '">' . sanitize_html($display_name) . '</a></li>';
                }
                if (empty($available_views)) {
                    debug_log("No views found to render in navigation. Check 'views/' directory.", 'WARNING');
                    echo '<li><span class="no-views-message">No views available.</span></li>';
                }
                ?>
            </ul>
        </nav>

        <div class="customer-selection">
            <label for="customer-select" class="sr-only">Select Customer:</label>
            <div class="select-wrapper">
                <select id="customer-select" class="customer-dropdown" name="customer_id">
                    <option value="">-- Select Customer --</option>
                    <?php
                    if (!empty($customers)) {
                        debug_log("Populating customer dropdown with " . count($customers) . " customers.", 'INFO');
                        foreach ($customers as $customer) {
                            $customer_id = sanitize_int($customer['id']);
                            $customer_name = sanitize_html($customer['name']);
                            $selected = ($customer_id === $current_customer_id) ? 'selected' : '';
                            echo '<option value="' . $customer_id . '" ' . $selected . '>' . $customer_name . '</option>';
                            debug_log("Added customer to dropdown: ID $customer_id, Name '$customer_name'.", 'DEBUG');
                        }
                    } else {
                        debug_log("No customer data available to populate dropdown.", 'WARNING');
                        echo '<option value="" disabled>No customers available</option>';
                    }
                    ?>
                </select>
                <input type="text" id="customer-search" class="customer-search-input" placeholder="Search customer..." aria-label="Search customer">
                <?php debug_log("Customer dropdown and search input rendered.", 'DEBUG'); ?>
            </div>
            <button id="apply-customer-filter" class="button cta-button">Apply Filter</button>
            <?php debug_log("Apply customer filter button rendered.", 'DEBUG'); ?>
        </div>
    </div>
</header>
<?php debug_log("Header.php rendering complete.", 'INFO'); ?>