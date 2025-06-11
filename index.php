<?php
/**
 * MPSM Dashboard - Main Entry Point
 *
 * This file serves as the primary entry point for the MPS Monitor Dashboard application.
 * It handles initial setup, configuration loading, routing to different views,
 * and the overall structure of the HTML page.
 *
 * Debugging Philosophy:
 * Every major step, from configuration loading to view rendering, should be
 * explicitly logged. This helps trace the application's flow and identify
 * where issues might arise during the request lifecycle.
 */

// 1. Core Application Setup and Configuration Loading
debug_log("Starting MPSM Dashboard application bootstrap.", 'INFO');

// Include the configuration file first. This defines global constants and settings.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
debug_log("Configuration loaded from config.php.", 'INFO');

// Include the global utility functions.
require_once APP_BASE_PATH . 'functions.php';
debug_log("Utility functions loaded from functions.php.", 'INFO');

// 2. Initialize Debugging
// The debug_log function is now available after functions.php is loaded.
// It's crucial to call debug_log for actions happening early in the bootstrap process.
debug_log("Debugging system initialized. DEBUG_MODE: " . (DEBUG_MODE ? 'ON' : 'OFF'), 'INFO');
if (DEBUG_LOG_TO_FILE) {
    debug_log("Logging to file enabled: " . DEBUG_LOG_FILE, 'INFO');
} else {
    debug_log("Logging to file disabled.", 'INFO');
}

// 3. Handle incoming requests and determine current view
// Get the requested view from the URL query parameter (e.g., ?view=service).
// Default to a 'dashboard' view if no specific view is requested.
$current_view_slug = get_get_param('view', 'dashboard');
debug_log("Requested view slug: '" . sanitize_html($current_view_slug) . "'", 'INFO');

// Get all available views to validate the requested view.
$available_views = get_available_views();

// Check if the requested view is valid. If not, default to 'dashboard' or a fallback view.
if (!array_key_exists($current_view_slug, $available_views)) {
    debug_log("Invalid view slug '" . sanitize_html($current_view_slug) . "' requested. Defaulting to 'dashboard'.", 'WARNING');
    $current_view_slug = 'dashboard';
    // If 'dashboard' also doesn't exist, this might be an issue.
    if (!array_key_exists('dashboard', $available_views)) {
        debug_log("Default 'dashboard' view not found either. Application may not display correctly.", 'ERROR');
        // As a last resort, if no view exists, we might show an error page or a blank screen.
        // For now, we'll just continue, and the main content area will be empty or show an error.
    }
}
debug_log("Current active view: '" . sanitize_html($current_view_slug) . "'", 'INFO');

// Store the selected customer in session (placeholder for future logic)
$selected_customer_id = get_get_param('customer_id', null, 'int');
if ($selected_customer_id !== null) {
    $_SESSION['selected_customer_id'] = $selected_customer_id;
    debug_log("Customer ID " . $selected_customer_id . " selected and stored in session.", 'INFO');
} else if (isset($_SESSION['selected_customer_id'])) {
    $selected_customer_id = $_SESSION['selected_customer_id'];
    debug_log("Customer ID " . $selected_customer_id . " loaded from session.", 'INFO');
} else {
    debug_log("No customer ID selected or found in session.", 'INFO');
}

// Prepare data to be passed to the header and views.
$header_data = [
    'db_status'  => get_db_status(),
    'api_status' => get_api_status(),
    'customers'  => get_customers(), // This will be used by the customer dropdown
    'current_customer_id' => $selected_customer_id,
    'available_views' => $available_views,
    'current_view_slug' => $current_view_slug,
];
debug_log("Header data prepared.", 'DEBUG');

// Prepare data for the current view.
// This is where you would fetch data specific to the selected view.
// For now, it's just a placeholder.
$view_data = [
    'selected_customer_id' => $selected_customer_id,
    // Add other view-specific data here later
];
debug_log("View data prepared.", 'DEBUG');

// 4. HTML Document Structure and Content Inclusion
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize_html(APP_NAME); ?> | <?php echo sanitize_html($available_views[$current_view_slug] ?? 'Dashboard'); ?></title>
    <meta name="description" content="A customizable user interface dashboard for MPS Monitor.">

    <link rel="stylesheet" href="<?php echo BASE_URL . CSS_PATH; ?>style.css?v=<?php echo APP_VERSION; ?>">

    <?php debug_log("HTML head section rendered.", 'DEBUG'); ?>
</head>
<body class="theme-dark"> <?php
    debug_log("Including header.php.", 'INFO');
    include_partial('header.php', $header_data);
    if (!file_exists(APP_BASE_PATH . 'header.php')) {
        debug_log("CRITICAL ERROR: header.php not found at expected path: " . APP_BASE_PATH . 'header.php', 'ERROR');
        if (DEBUG_MODE) {
            echo "<div class='critical-error-banner'>CRITICAL ERROR: Header file missing. Please check file paths.</div>";
        }
    }
    ?>

    <main class="dashboard-main-content">
        <?php debug_log("Main content area started.", 'INFO'); ?>
        <section class="view-container">
            <h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug] ?? 'Unknown View'); ?></h2>
            <div class="cards-grid">
                <?php
                // Include the selected view file.
                // The view file is responsible for deciding which cards to render.
                $view_file_path = VIEWS_PATH . sanitize_url($current_view_slug) . '.php'; // Use sanitize_url for security
                debug_log("Attempting to include view file: " . $view_file_path, 'INFO');

                if (strpos(realpath($view_file_path), realpath(VIEWS_PATH)) === 0 && file_exists($view_file_path)) {
                    // Extract view_data into the scope of the included view file.
                    extract($view_data);
                    include $view_file_path;
                    debug_log("Successfully included view: " . sanitize_html($current_view_slug), 'INFO');
                } else {
                    debug_log("View file not found for slug '" . sanitize_html($current_view_slug) . "' at: " . $view_file_path, 'ERROR');
                    echo "<div class='card error-card'>
                            <h3>View Not Found!</h3>
                            <p>The requested view '<strong>" . sanitize_html($current_view_slug) . "</strong>' could not be loaded.</p>
                            <p>Please check the URL or ensure the view file exists in the <code>" . sanitize_html(basename(VIEWS_PATH)) . "/</code> directory.</p>
                          </div>";
                }
                ?>
            </div>
        </section>
        <?php debug_log("Main content area ended.", 'INFO'); ?>
    </main>

    <?php
    debug_log("Including footer.php.", 'INFO');
    include_partial('footer.php');
    if (!file_exists(APP_BASE_PATH . 'footer.php')) {
        debug_log("CRITICAL ERROR: footer.php not found at expected path: " . APP_BASE_PATH . 'footer.php', 'ERROR');
        if (DEBUG_MODE) {
            echo "<div class='critical-error-banner'>CRITICAL ERROR: Footer file missing. Please check file paths.</div>";
        }
    }
    ?>

    <script src="<?php echo BASE_URL . JS_PATH; ?>script.js?v=<?php echo APP_VERSION; ?>"></script>
    <?php debug_log("JavaScript files linked.", 'DEBUG'); ?>

</body>
</html>
<?php
debug_log("MPSM Dashboard application finished rendering.", 'INFO');
// End of index.php