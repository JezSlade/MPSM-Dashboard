<?php
// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// --- Cache Control Headers ---
// These headers prevent the browser from caching the page,
// ensuring that the latest version is always loaded.
// This is crucial for dynamic dashboards where data changes frequently.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past to ensure expiration.
// --- End Cache Control Headers ---

// Start the PHP session.
// Sessions are used to store user-specific data across multiple page requests.
session_start();

// Include core configuration and helper functions/classes.
// These files provide essential settings, utility functions,
// and the main classes for managing the dashboard's state and file operations.
require_once 'config.php'; // Contains global constants and widget discovery logic.
require_once 'helpers.php'; // Provides utility functions like render_widget().
require_once 'src/php/DashboardManager.php'; // Manages dashboard settings and widget states.
require_once 'src/php/FileManager.php'; // Handles file operations, especially for widget templates.

// Instantiate the DashboardManager.
// This object is responsible for loading, saving, and managing the dashboard's configuration.
// $available_widgets is a global variable populated by the discover_widgets() function in config.php.
$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, $available_widgets);

// Load the current dashboard state.
// This includes global settings (title, accent color, etc.) and the state of all widgets.
$current_dashboard_state = $dashboardManager->loadDashboardState();
$settings = $current_dashboard_state; // Assign the loaded state to $settings for easier access.

// IMPORTANT: Initialize $_SESSION['dashboard_settings'] from the loaded state.
// This step is critical to synchronize the PHP session with the persistent dashboard settings.
// It ensures that any changes made via AJAX and saved to the JSON file are reflected
// in the session when the page is reloaded or initially loaded.
$_SESSION['dashboard_settings'] = $current_dashboard_state;

// Define the current version numbers for the dashboard.
// Based on the user's request "v 00.1.2.47", the version segments are reordered and assigned.
// Here, '00' is treated as the build number, '1' as the major, '2' as the minor, and '47' as the patch.
// These are static values within this PHP file. For dynamic versioning (e.g., build number rolling over),
// a more robust system (like reading from a file, database, or build environment variables) would be needed.
$build_number = '00'; // Corresponds to '00' in v00.1.2.47
$major_version = 1;    // Corresponds to '1' in v00.1.2.47
$minor_version = 2;    // Corresponds to '2' in v00.1.2.47
$patch_version = 47;   // Corresponds to '47' in v00.1.2.47

/**
 * Formats the dashboard version string according to the "build.major.minor.patch" scheme.
 *
 * @param string $build Build identifier (e.g., '00').
 * @param int $major Major version number.
 * @param int $minor Minor version number.
 * @param int $patch Patch version number.
 * @return string Formatted version string (e.g., "v00.1.2.47").
 */
function formatDashboardVersion($build, $major, $minor, $patch) {
    return "v{$build}.{$major}.{$minor}.{$patch}";
}

$formattedVersion = formatDashboardVersion(
    $build_number,
    $major_version,
    $minor_version,
    $patch_version
);

// The index.php no longer handles POST requests directly for actions.
// In the refactored architecture, all dashboard actions (saving settings,
// managing widgets, creating new widgets) are handled by dedicated API endpoints
// located in the `api/` directory. These endpoints are accessed via AJAX requests
// from the frontend JavaScript.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This block is largely vestigial and should ideally not be reached for
    // typical dashboard interactions. If it is, it indicates a direct POST
    // to index.php, which is not the intended flow for dynamic actions.
    // Any POST requests here would likely be for initial form submissions
    // or legacy functionalities that should be migrated to AJAX APIs.
    // For now, it simply prevents any unintended processing.
    error_log("Received a direct POST request to index.php. All dashboard actions should use AJAX to API endpoints.");
    // Optionally, you could redirect or return an error here if direct POSTs are strictly forbidden.
}

// Ensure the $settings array used for rendering always reflects the latest state.
// This line merges the state loaded from the JSON file with any potential
// session-based overrides. This is a safeguard to ensure the most up-to-date
// configuration is used for rendering the HTML.
$settings = array_replace_recursive($dashboardManager->loadDashboardState(), $_SESSION['dashboard_settings'] ?? []);

// Pass available widgets to the view
// The $available_widgets variable contains metadata for all discovered widget templates.
// It's made global to be accessible throughout the PHP rendering process.
global $available_widgets; // Ensure $available_widgets from config.php is accessible.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main dashboard CSS -->
    <link rel="stylesheet" href="dashboard.css">
    <!-- Chart.js CDN for charting widgets -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <style>
        /* CSS variables for dynamic styling based on dashboard settings */
        :root {
            --accent: <?= $settings['accent_color'] ?>; /* Main accent color */
            --glass-bg: rgba(35, 40, 49, <?= $settings['glass_intensity'] ?>); /* Background color with transparency for glass effect */
            --blur-amount: <?= $settings['blur_amount'] ?>; /* Blur intensity for glass effect */
            --transition: all 0.3s ease-in-out; /* Standard transition for animations */
            /* Define accent-rgb for use in rgba() for shadows/glows */
            --accent-rgb: <?= implode(',', sscanf($settings['accent_color'], '#%02x%02x%02x')) ?>;
        }

        /* Widget specific styling and animation control */
        .widget {
            transition: <?= $settings['enable_animations'] ? 'var(--transition)' : 'none' ?>; /* Apply transition only if animations are enabled */
        }
        .widget:hover {
            <?php if ($settings['enable_animations']): ?>
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 12px 12px 24px var(--shadow-dark), -12px -12px 24px rgba(74, 78, 94, 0.1); /* Enhanced shadow on hover */
            <?php endif; ?>
        }

        /* Settings Panel specific styles to ensure save button is always visible */
        .settings-panel {
            display: flex;
            flex-direction: column; /* Stack children vertically */
            /* Existing styles for position, width, height, background, etc. */
            position: fixed;
            top: 0;
            right: -400px; /* Hidden by default */
            width: 380px;
            height: 100%;
            background: var(--modal-bg);
            box-shadow: -5px 0 15px rgba(0,0,0,0.3);
            transition: right 0.3s ease-in-out;
            z-index: 1001; /* Above overlay */
            border-left: 1px solid var(--modal-border);
        }

        .settings-panel.open { /* Class to make the panel visible */
            right: 0;
        }

        .settings-form-content { /* This new class wraps the scrollable form body */
            padding: 20px;
            overflow-y: auto; /* Make this section scrollable */
            flex-grow: 1; /* Allows it to take up available space */
        }

        .settings-footer { /* New footer for the save button */
            padding: 15px 20px;
            border-top: 1px solid var(--modal-border);
            background-color: var(--modal-header-bg); /* Match header or modal background */
            flex-shrink: 0; /* Prevent it from shrinking */
        }

        .overlay { /* Ensure overlay also has a transition for smoother appearance */
            transition: opacity 0.3s ease;
        }
        .overlay.active {
            display: block; /* Make sure it becomes block when active */
            opacity: 1;
        }

        /* MODAL OVERLAYS - Ensure they are hidden by default and only become flex when active */
        .message-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Darker background for modals */
            backdrop-filter: blur(8px); /* Blur effect for modals */
            z-index: 2000; /* Higher than settings panel */
            display: none; /* Crucial: Hidden by default */
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .message-modal-overlay.active {
            display: flex; /* Only show as flex when active */
            opacity: 1;
        }

        .message-modal {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
            width: 90%;
            max-width: 500px; /* Standard modal width */
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            display: flex; /* Ensure modal content itself is flex for header/body/footer structure */
            flex-direction: column;
            max-height: 90vh; /* Prevent modal from exceeding viewport height */
        }

        .message-modal-overlay.active .message-modal {
            transform: translateY(0);
            opacity: 1;
        }

        .message-modal.large-modal {
            max-width: 800px; /* Larger modal for widget management */
        }

        .message-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--bg-primary); /* Darker header for modals */
            border-bottom: 1px solid var(--glass-border);
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }

        .message-modal-header h3 {
            margin: 0;
            color: var(--primary);
            font-size: 1.2em;
        }

        .message-modal-body {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto; /* Make modal body scrollable if content overflows */
            color: var(--text-primary);
        }

        .message-modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--glass-border);
            background-color: var(--bg-primary);
            border-bottom-left-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
            text-align: right;
        }

        .btn-close-modal {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.8em;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .btn-close-modal:hover {
            color: var(--danger);
        }
    </style>
</head>
<body>
    <!-- ALL MODAL OVERLAYS SHOULD BE DIRECT CHILDREN OF BODY -->

    <!-- New: Overlay for expanded widgets -->
    <div class="widget-expanded-overlay" id="widget-expanded-overlay"></div>

    <!-- Widget Settings Modal Structure (for single widget dimensions) -->
    <div class="message-modal-overlay" id="widget-settings-modal-overlay">
        <div class="message-modal" id="widget-settings-modal">
            <div class="message-modal-header">
                <h3 id="widget-settings-modal-title">Widget Settings</h3>
                <button class="btn-close-modal" id="close-widget-settings-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <form id="widget-dimensions-form">
                    <!-- Changed from widget_index to widget_id -->
                    <input type="hidden" id="widget-settings-id" name="widget_id">
                    <div class="form-group">
                        <label for="widget-settings-width">Width (Grid Units)</label>
                        <input type="number" id="widget-settings-width" name="new_width" min="0.5" max="3" step="0.5" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="widget-settings-height">Height (Grid Units)</label>
                        <input type="number" id="widget-settings-height" name="new_height" min="0.5" max="4" step="0.5" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Save Dimensions</button>
                </form>
            </div>
        </div>
    </div>
    <!-- END Widget Settings Modal Structure -->

    <!-- NEW: Widget Management Modal Structure (Consolidated Widget Settings) -->
    <div class="message-modal-overlay" id="widget-management-modal-overlay">
        <div class="message-modal large-modal" id="widget-management-modal">
            <div class="message-modal-header">
                <h3 id="widget-management-modal-title">Widget Management</h3>
                <button class="btn-close-modal" id="close-widget-management-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="widget-management-table">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Width</th>
                                <th>Height</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody id="widget-management-table-body">
                            <!-- Widget data will be populated here by JavaScript -->
                            <tr><td colspan="6" style="text-align: center; padding: 20px;">Loading widgets...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="message-modal-footer">
                <button class="btn btn-primary" id="save-widget-management-changes-btn">Save All Widget Changes</button>
            </div>
        </div>
    </div>
    <!-- END NEW: Widget Management Modal Structure -->

    <!-- NEW: Create New Widget Modal Structure -->
    <div class="message-modal-overlay" id="create-widget-modal-overlay">
        <div class="message-modal" id="create-widget-modal">
            <div class="message-modal-header">
                <h3>Create New Widget Template</h3>
                <button class="btn-close-modal" id="close-create-widget-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <form id="create-widget-form">
                    <div class="form-group">
                        <label for="new-widget-name">Widget Name</label>
                        <input type="text" id="new-widget-name" name="name" class="form-control" placeholder="e.g., My Custom Chart" required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-id">Widget ID (lowercase, no spaces, e.g., my_custom_chart)</label>
                        <input type="text" id="new-widget-id" name="id" class="form-control" placeholder="e.g., my_custom_chart" pattern="^[a-z0-9_]+$" title="Lowercase letters, numbers, and underscores only." required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-icon">Font Awesome Icon (e.g., fas fa-chart-bar)</label>
                        <!-- MODIFIED: Placeholder and value now expect full class -->
                        <input type="text" id="new-widget-icon" name="icon" class="form-control" value="fas fa-cube" placeholder="e.g., fas fa-chart-bar">
                    </div>
                    <div class="form-group">
                        <label for="new-widget-width">Default Width (0.5-3.0 grid units)</label>
                        <input type="number" id="new-widget-width" name="width" class="form-control" value="1.0" min="0.5" max="3" step="0.5" required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-height">Default Height (0.5-4.0 grid units)</label>
                        <input type="number" id="new-widget-height" name="height" class="form-control" value="1.0" min="0.5" max="4" step="0.5" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-file-code"></i> Create Widget Template
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- END NEW: Create New Widget Modal Structure -->

    <!-- Simple Message Modal Structure (for general confirmations/alerts) -->
    <div class="message-modal-overlay" id="message-modal-overlay">
        <div class="message-modal">
            <div class="message-modal-header">
                <h3 id="message-modal-title"></h3>
                <button class="btn-close-modal" id="close-message-modal">&times;</button>
            </div>
            <div class="message-modal-body">
                <p id="message-modal-content"></p>
            </div>
            <div class="message-modal-footer">
                <button class="btn btn-primary" id="confirm-message-modal">OK</button>
            </div>
        </div>
    </div>

    <div class="dashboard">
        <!-- Sidebar Toggle Button - Moved outside the sidebar -->
        <div class="sidebar-toggle" id="sidebar-toggle">
            <i class="fas fa-chevron-left"></i>
        </div>

        <!-- Dashboard Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <!-- Use the site_icon setting here -->
                    <!-- MODIFIED: Use full class for site_icon -->
                    <i class="<?= htmlspecialchars($settings['site_icon'] ?? 'fas fa-gem') ?>"></i>
                </div>
                <div class="logo-text"><?= htmlspecialchars($settings['title']) ?></div>
                <div class="logo-version" style="font-size: 0.75em; color: #bbb; margin-left: 8px;" id="version-display">
                    <?php echo $formattedVersion ?>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn" id="settings-toggle">
                    <i class="fas fa-cog"></i> Settings
                </button>
                <button class="btn" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </header>

        <!-- Dashboard Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-section">
                <div class="section-title">Navigation</div>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <!-- Re-added Theme Library nav-item to open a new tab -->
                <a href="theme_library.html" target="_blank" class="nav-item">
                    <i class="fas fa-layer-group"></i>
                    <span>Theme Library</span>
                </a>
                <!-- REMOVED: Users nav item as per request -->
                <!-- <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div> -->
                <div class="nav-item" id="widget-management-nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Widget Management</span>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php
                    // Get a list of currently active widget IDs
                    $active_widget_ids = array_keys(array_filter($settings['widgets_state'], function($widget) {
                        return $widget['is_active'];
                    }));

                    foreach ($available_widgets as $id => $widget):
                        // Only display the widget in the library if it's NOT currently active on the dashboard
                        if (!in_array($id, $active_widget_ids)):
                    ?>
                    <div class="widget-item" draggable="true" data-widget-id="<?= $id ?>">
                        <i class="<?= htmlspecialchars($widget['icon']) ?>"></i>
                        <div class="widget-name"><?= $widget['name'] ?></div>
                    </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- REMOVED: Dashboard Settings section as per request -->
            <!--
            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="general-settings-nav-item">
                    <i class="fas fa-cog"></i>
                    <span>General Settings</span>
                </div>
                <div class="nav-item" id="layout-settings-nav-item">
                    <i class="fas fa-columns"></i>
                    <span>Layout Settings</span>
                </div>
                <div class="nav-item" id="advanced-settings-nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
            -->
        </aside>

        <!-- Main Content Area -->
        <main class="main-content" id="widget-container">
            <?php
            // Filter widgets to render based on 'show_all_available_widgets' and 'is_active'
            $widgets_to_render = [];
            if ($settings['show_all_available_widgets']) {
                // If 'show all' is true, render ALL discovered widgets
                $widgets_to_render = array_values($settings['widgets_state']);
            } else {
                // Otherwise, render only widgets marked as active
                foreach ($settings['widgets_state'] as $widget_id => $widget_data) {
                    if ($widget_data['is_active']) {
                        $widgets_to_render[] = $widget_data;
                    }
                }
            }

            // Sort widgets for rendering by their 'position'
            usort($widgets_to_render, function($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            foreach ($widgets_to_render as $widget):
                $widget_id = $widget['id'];
                // Use the dimensions from the widgets_state array
                $current_width_user_facing = max(0.5, min(3.0, (float)($widget['width'])));
                $current_height_user_facing = max(0.5, min(4.0, (float)($widget['height'])));

                // Convert user-facing units to internal grid units (doubled for half-unit precision)
                $current_width_internal = $current_width_user_facing * 2;
                $current_height_internal = $current_height_user_facing * 2;
            ?>
            <!-- Widget container, made draggable for reordering -->
            <div class="widget"
                 draggable="true"
                 style="--width: <?= $current_width_internal ?>; --height: <?= $current_height_internal ?>;"
                 data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                 data-current-width="<?= $current_width_user_facing ?>"
                 data-current-height="<?= $current_height_user_facing ?>">
                <!-- This placeholder div marks the widget's original position in the DOM -->
                <div class="widget-placeholder" data-original-parent-id="widget-container" data-original-id="<?= htmlspecialchars($widget_id) ?>"></div>

                <div class="widget-header">
                    <div class="widget-title">
                        <!-- MODIFIED: Use full class for widget header icon -->
                        <i class="<?= htmlspecialchars($widget['icon']) ?>"></i>
                        <span><?= htmlspecialchars($widget['name']) ?></span>
                    </div>
                    <div class="widget-actions">
                        <!-- Add data attributes to identify actions -->
                        <div class="widget-action action-settings"
                            data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                            data-current-width="<?= $current_width_user_facing ?>"
                            data-current-height="<?= $current_height_user_facing ?>"
                            title="Adjust widget dimensions">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="widget-action action-expand">
                            <i class="fas fa-expand"></i>
                        </div>
                        <!-- Remove button now triggers deactivation -->
                        <div class="widget-action remove-widget"
                            data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                            title="Deactivate widget">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <?= render_widget($widget_id) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>


    <!-- Settings Panel (Global Dashboard Settings) -->
    <div class="overlay" id="settings-overlay"></div>
    <div class="settings-panel" id="settings-panel">
        <div class="settings-header">
            <h2>Dashboard Settings</h2>
            <button class="btn" id="close-settings">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Settings Panel Navigation Tabs -->
        <div class="settings-tabs">
            <button class="settings-tab-btn active" data-target="general-settings-section">General</button>
            <button class="settings-tab-btn" data-target="layout-settings-section">Layout</button>
            <button class="settings-tab-btn" data-target="advanced-settings-section">Advanced</button>
        </div>

        <form id="global-settings-form" method="post" class="settings-form-content">
            <!-- General Settings Section -->
            <div class="settings-section active" id="general-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">General Settings</h3>

                    <div class="form-group">
                        <label for="dashboard_title">Dashboard Title</label>
                        <input type="text" id="dashboard_title" name="dashboard_title"
                            class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="site_icon">Site Icon (Font Awesome class, e.g., fas fa-gem)</label>
                        <!-- MODIFIED: Value now expects full class -->
                        <input type="text" id="site_icon" name="site_icon"
                            class="form-control" value="<?= htmlspecialchars($settings['site_icon'] ?? 'fas fa-gem') ?>" placeholder="e.g., fas fa-gem">
                    </div>

                    <div class="form-group">
                        <label for="accent_color">Accent Color</label>
                        <input type="color" id="accent_color" name="accent_color"
                            class="form-control" value="<?= $settings['accent_color'] ?>" style="height: 50px;">
                    </div>

                    <div class="form-group">
                        <label>Enable Animations</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_animations"
                                <?= $settings['enable_animations'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-group">
                    <h3 class="settings-title">Glass Effect</h3>

                    <div class="form-group">
                        <label for="glass_intensity">Glass Intensity</label>
                        <input type="range" id="glass_intensity" name="glass_intensity"
                            class="form-control" min="0.1" max="0.9" step="0.05"
                            value="<?= $settings['glass_intensity'] ?>">
                    </div>

                    <div class="form-group">
                        <label for="blur_amount">Blur Amount</labeSl>
                        <select id="blur_amount" name="blur_amount" class="form-control">
                            <option value="5px" <?= $settings['blur_amount'] == '5px' ? 'selected' : '' ?>>Subtle (5px)</option>
                            <option value="10px" <?= $settings['blur_amount'] == '10px' ? 'selected' : '' ?>>Standard (10px)</option>
                            <option value="15px" <?= $settings['blur_amount'] == '15px' ? 'selected' : '' ?>>Strong (15px)</option>
                            <option value="20px" <?= $settings['blur_amount'] == '20px' ? 'selected' : '' ?>>Extra Strong (20px)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Layout Settings Section -->
            <div class="settings-section" id="layout-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">Widget Layout</h3>
                    <!-- Show All Available Widgets Toggle -->
                    <div class="form-group">
                        <label>Show All Available Widgets (Overrides active/inactive status)</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="show_all_available_widgets" id="show_all_available_widgets"
                                <?= $settings['show_all_available_widgets'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="settings-group">
                    <h3 class="settings-title">Add Existing Widget</h3>
                    <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 15px;">
                        Use Widget Management to activate/deactivate widgets.
                    </p>
                    <button type="button" class="btn" style="width: 100%;" disabled>
                        <i class="fas fa-info-circle"></i> Use Widget Management
                    </button>
                </div>

                <!-- Create New Widget Template Button -->
                <div class="settings-group">
                    <h3 class="settings-title">Create New Widget</h3>
                    <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 15px;">
                        Generate a new blank widget file ready for your custom code.
                    </p>
                    <button type="button" class="btn btn-primary" id="open-create-widget-modal" style="width: 100%;">
                        <i class="fas fa-file-code"></i> Create New Widget Template
                    </button>
                </div>
            </div>

            <!-- Advanced Settings Section -->
            <div class="settings-section" id="advanced-settings-section">
                <div class="settings-group">
                    <h3 class="settings-title">Advanced Options</h3>
                    <div class="form-group">
                        <label>Export Configuration</label>
                        <button class="btn" id="export-settings-btn" style="width: 100%;">
                            <i class="fas fa-download"></i> Download Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Import Configuration</label>
                        <input type="file" class="form-control" id="import-settings-file-input">
                        <button type="button" class="btn btn-primary" id="import-settings-btn" style="width: 100%; margin-top: 10px;">
                            <i class="fas fa-upload"></i> Upload Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Output Current Settings (JSON)</label>
                        <button type="button" class="btn" id="output-settings-json-btn" style="width: 100%;">
                            <i class="fas fa-code"></i> Show Current Settings
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Output Active Widgets (JSON)</label>
                        <button type="button" class="btn" id="output-active-widgets-json-btn" style="width: 100%;">
                            <i class="fas fa-list"></i> Show Active Widgets
                        </button>
                    </div>

                    <!-- Delete Settings JSON Button -->
                    <div class="form-group">
                        <label>Reset Dashboard</label>
                        <button type="button" id="delete-settings-json-btn" class="btn btn-danger" style="width: 100%;">
                            <i class="fas fa-trash-alt"></i> Delete Settings JSON (Reset All)
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="settings-footer">
            <button type="submit" form="global-settings-form" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </div>
    </div>

    <script type="module" src="src/js/main.js"></script>
    <script src="version.js"></script>
</body>
</html>

<?php
// Commented out the deprecated Theme Component Library Modal HTML and related JS
// This ensures the structure is preserved but not active in the DOM.
/*
<div class="message-modal" id="theme-modal" style="display: none;">
    <div class="message-modal-header">
        <h2>Theme Component Library</h2>
        <button class="btn btn-danger" onclick="closeThemeModal()">×</button>
    </div>
    <div class="message-modal-body">
        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Buttons</h3>
            <button class="btn btn-primary">Primary Button</button>
            <button class="btn btn-secondary">Secondary</button>
            <button class="btn btn-outline">Outline</button>
            <button class="btn btn-danger">Danger</button>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Input Fields</h3>
            <input type="text" class="form-control" placeholder="Text input">
            <input type="password" class="form-control" placeholder="Password">
            <select class="form-control">
                <option>Option 1</option>
                <option>Option 2</option>
            </select>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Cards</h3>
            <div class="card">
                <div class="card-header">Card Title</div>
                <div class="card-body">This is the body of a neumorphic card.</div>
            </div>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Badges & Tags</h3>
            <span class="badge badge-success">Success</span>
            <span class="badge badge-warning">Warning</span>
            <span class="badge badge-info">Info</span>
        </section>

        <section class="theme-demo-block">
            <h3 class="widget-subtitle">Progress & Loaders</h3>
            <div class="progress-bar"><div class="progress" style="width: 60%;"></div></div>
            <div class="loader"></div>
        </section>
    </div>
</div>

<script>
// These functions are no longer active, as the modal is commented out.
// function openThemeModal() {
//     document.getElementById('theme-modal').style.display = 'block';
// }
// function closeThemeModal() {
//     document.getElementById('theme-modal').style.display = 'none';
// }
</script>
<script src="src/js/themeLibrary.js"></script>
*/
?>
