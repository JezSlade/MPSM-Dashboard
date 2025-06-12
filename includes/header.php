<?php
/**
 * includes/header.php
 *
 * Dashboard Header Partial
 *
 * Renders:
 * - Application title (APP_NAME)
 * - Database & API status indicators
 * - Theme toggle
 * - Customer selection dropdown (glassmorphic)
 *
 * NOTE: Navigation is handled by includes/navigation.php
 */

// Fallbacks in case variables weren’t passed in
$db_status           = $db_status           ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$api_status          = $api_status          ?? ['status' => 'unknown', 'message' => 'Status not retrieved.'];
$customers           = $customers           ?? [];
$current_customer_id = $current_customer_id ?? null;
$available_views     = $available_views     ?? []; // Still available for APP_NAME/title, but not used by this header for nav
$current_view_slug   = $current_view_slug   ?? 'dashboard';

debug_log("Rendering header", 'DEBUG');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize_html(APP_NAME); ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js" defer></script>
</head>
<body>
    <div id="wrapper">
        <header class="dashboard-header glassmorphic">
            <div class="header-top">
                <div class="app-branding">
                    <h1><?php echo sanitize_html(APP_NAME); ?></h1>
                </div>
                <div class="status-indicators">
                    <div class="status-item db-status">
                        <span
                            class="status-dot status-<?php echo sanitize_html($db_status['status']); ?>"
                            title="Database: <?php echo sanitize_html($db_status['message']); ?>"
                        ></span>
                        <span>Database</span>
                    </div>
                    <div class="status-item api-status">
                        <span
                            class="status-dot status-<?php echo sanitize_html($api_status['status']); ?>"
                            title="API: Status not retrieved."
                        ></span>
                        <span>API</span>
                    </div>
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Theme">
                        <span class="icon-light">☀️</span>
                        <span class="icon-dark">🌙</span>
                    </button>
                </div>
            </div>

            <div class="header-bottom">
                <div class="customer-selection">
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
                                <option disabled>No customers available</option>
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