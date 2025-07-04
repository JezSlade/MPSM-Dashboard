<?php
// config.php

// Define the path to the dashboard settings file
// This file will store the active widgets and dashboard preferences
if (!defined('DASHBOARD_SETTINGS_FILE')) {
    define('DASHBOARD_SETTINGS_FILE', __DIR__ . '/dashboard_settings.json');
}
// Removed: define('DYNAMIC_WIDGETS_FILE', __DIR__ . '/dynamic_widgets.json'); // This file is no longer needed for dynamic widget definitions

// Define the application root directory for security
// IMPORTANT: Use !defined() to prevent "Constant APP_ROOT already defined" warnings
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

// Define Dashboard Version Constants
// These are used for displaying the dashboard version in the UI
if (!defined('DASHBOARD_VERSION_MAJOR')) {
    define('DASHBOARD_VERSION_MAJOR', 1);
}
if (!defined('DASHBOARD_VERSION_MINOR')) {
    define('DASHBOARD_VERSION_MINOR', 0);
}
if (!defined('DASHBOARD_VERSION_PATCH')) {
    define('DASHBOARD_VERSION_PATCH', 0);
}
// This build number can be dynamically updated, e.g., by CI/CD pipeline
if (!defined('DASHBOARD_VERSION_BUILD')) {
    define('DASHBOARD_VERSION_BUILD', 1); // Increment this for each build/deployment
}


// Include helper functions for widget discovery
require_once APP_ROOT . '/helpers.php';

/**
 * All available widgets in the system.
 * This list is now dynamically populated by scanning the 'widgets/' directory.
 * Metadata (name, icon, width, height) is extracted from the widget's PHP file.
 *
 * 'name': Display name for the widget
 * 'icon': Font Awesome 6 icon class (e.g., 'chart-line', 'bell', 'print')
 * 'width': Default grid columns the widget spans (1-3)
 * 'height': Default grid rows the widget spans (1-4)
 */
$available_widgets = discover_widgets();

// Example of how to structure a widget PHP file for metadata extraction:
/*
// widgets/my_new_widget.php
<?php
// Widget Name: My New Custom Widget
// Widget Icon: star
// Widget Width: 1.5
// Widget Height: 2.0
?>
<div class="compact-content">
    <h3>My New Widget</h3>
    <p>This is the compact view.</p>
</div>
<div class="expanded-content">
    <h3>My New Custom Widget (Expanded)</h3>
    <p>This is the detailed view of my new widget.</p>
</div>
*/
