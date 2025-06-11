<?php
// MPSM Dashboard - index.php

// 1. Configuration and Utility Inclusion
// Make sure your config.php defines APP_BASE_PATH, BASE_URL, JS_PATH, CSS_PATH, APP_VERSION
// Example:
// define('APP_BASE_PATH', __DIR__ . '/'); // If index.php is in the root
// define('BASE_URL', 'http://yourdomain.com/');
// define('JS_PATH', 'js/');
// define('CSS_PATH', 'css/');
// define('APP_VERSION', '1.0.0'); // This APP_VERSION is a PHP constant, separate from JS version.js
// define('DEBUG_MODE', true); // Example
// define('APP_NAME', 'MPSM Dashboard'); // Example

require_once 'config.php';
require_once 'functions.php'; // Assuming debug_log, sanitize_html, sanitize_url, include_partial are here

// 2. Session Management (if used)
// session_start(); // Uncomment if you are using sessions

// 3. Routing and View Selection
$current_view_slug = $_GET['view'] ?? 'dashboard'; // Default to 'dashboard' view

$available_views = [
    'dashboard' => 'Dashboard Overview',
    'reports' => 'Detailed Reports',
    // Add other views as they are created
];

// Data to be passed to header, views, and footer
$header_data = [
    'app_name' => APP_NAME,
    'current_view_title' => $available_views[$current_view_slug] ?? 'Dashboard',
];

$view_data = [
    // Data specific to the view can be populated here based on $current_view_slug
    // For example, fetching data from a database
    'cards' => [
        // Example structure for cards
        ['title' => 'Total Printers', 'value' => '1200', 'icon' => 'printer'],
        ['title' => 'Devices Online', 'value' => '1150', 'icon' => 'wifi'],
        // ... more dynamic card data
    ],
];

debug_log("Application started. Current view: " . sanitize_html($current_view_slug), 'INFO');

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
<body class="theme-dark">
    <?php
    debug_log("Including header.php.", 'INFO');
    // Assuming header.php is in the root, or include_partial handles it correctly
    include_partial('header.php', $header_data);
    ?>

    <main class="dashboard-main-content">
        <?php debug_log("Main content area started.", 'INFO'); ?>
        <section class="view-container">
            <h2 class="view-title"><?php echo sanitize_html($available_views[$current_view_slug] ?? 'Unknown View'); ?></h2>
            <div class="cards-grid">
                <?php
                // Include the selected view file.
                // The view file is responsible for deciding which cards to render.
                $view_file_path = VIEWS_PATH . sanitize_url($current_view_slug) . '.php'; // Using VIEWS_PATH constant

                debug_log("Attempting to include view file: " . $view_file_path, 'INFO');

                // Basic security check: ensure the path is within the intended views directory
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
    // Assuming footer.php is in the root, or include_partial handles it correctly
    include_partial('footer.php');
    ?>

    <script src="/version.js?v=<?php echo APP_VERSION; ?>"></script> <script src="<?php echo BASE_URL . JS_PATH; ?>script.js?v=<?php echo APP_VERSION; ?>"></script>
    <?php debug_log("JavaScript files linked.", 'DEBUG'); ?>

</body>
</html>
<?php
debug_log("MPSM Dashboard application finished rendering.", 'INFO');
// End of index.php