<?php
// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// --- Cache Control Headers ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
// --- End Cache Control Headers ---

session_start();

// Include configuration and helper functions/classes
require_once 'config.php';
require_once 'helpers.php';
require_once 'src/php/DashboardManager.php';

// Instantiate DashboardManager
$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, DYNAMIC_WIDGETS_FILE, $available_widgets);

// Load current dashboard state (settings + active widgets)
$current_dashboard_state = $dashboardManager->loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'active_widgets' with dimensions

// IMPORTANT: Initialize $_SESSION['active_widgets'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['active_widgets'] = $current_dashboard_state['active_widgets'];
$_SESSION['dashboard_settings'] = $current_dashboard_state; // Sync all settings to session for current request

// The index.php no longer handles POST requests directly for actions.
// All actions are now handled by dedicated API endpoints via AJAX.
// This block is left empty as a reminder.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This part should ideally not be reached for action_type POSTs
    // as AJAX requests are now handled by api/dashboard.php etc.
    // If a non-AJAX POST comes here, it means a direct form submission
    // and the page will simply reload with the current state.
    // For robust handling, you might redirect to GET or show a message.
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by AJAX and then reloaded via loadDashboardState().
// This merge ensures default values are applied, then persistent ones, then session ones.
$settings = array_replace_recursive($dashboardManager->loadDashboardState(), $_SESSION['dashboard_settings'] ?? []);
// The 'active_widgets' for rendering should always come from the final $_SESSION state after processing
$settings['active_widgets'] = $_SESSION['active_widgets'];

// Pass available widgets to the view
global $available_widgets; // Ensure $available_widgets from config.php is accessible

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        :root {
            --accent: <?= $settings['accent_color'] ?>;
            --glass-bg: rgba(35, 40, 49, <?= $settings['glass_intensity'] ?>);
            --blur-amount: <?= $settings['blur_amount'] ?>;
        }
        .widget {
            transition: <?= $settings['enable_animations'] ? 'var(--transition)' : 'none' ?>;
        }
        .widget:hover {
            <?php if ($settings['enable_animations']): ?>
            transform: translateY(-5px);
            box-shadow:
                12px 12px 24px var(--shadow-dark),
                -12px -12px 24px rgba(74, 78, 94, 0.1);
            <?php endif; ?>
        }
    </style>
</head>
<body>
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
                    <input type="hidden" id="widget-settings-index" name="widget_index">
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
                                <th>Save Status</th>
                                <th>Deactivate</th>
                            </tr>
                        </thead>
                        <tbody id="widget-management-table-body">
                            <!-- Widget data will be populated here by JavaScript -->
                            <tr><td colspan="7" style="text-align: center; padding: 20px;">Loading widgets...</td></tr>
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
                        <label for="new-widget-icon">Font Awesome Icon (e.g., chart-bar)</label>
                        <input type="text" id="new-widget-icon" name="icon" class="form-control" value="cube" placeholder="e.g., chart-bar">
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
                        <i class="fas fa-plus"></i> Create Widget Template
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- END NEW: Create New Widget Modal Structure -->

    <div class="dashboard">
        <!-- Dashboard Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="logo-text"><?= htmlspecialchars($settings['title']) ?></div>
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
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="section-title">Navigation</div>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Analytics</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div>
                <div class="nav-item" id="widget-management-nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Widget Management</span>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php foreach ($available_widgets as $id => $widget): ?>
                    <div class="widget-item" draggable="true" data-widget-id="<?= $id ?>">
                        <i class="fas fa-<?= $widget['icon'] ?>"></i>
                        <div class="widget-name"><?= $widget['name'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="theme-settings-btn">
                    <i class="fas fa-palette"></i>
                    <span>Theme Settings</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-columns"></i>
                    <span>Layout Options</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content" id="widget-container">
            <?php
            // Determine which widgets to display based on 'show_all_available_widgets' setting
            $widgets_to_render = [];
            if ($settings['show_all_available_widgets']) {
                // If 'show all' is true, render ALL available widgets from config.php
                foreach ($available_widgets as $id => $def) {
                    $widgets_to_render[] = [
                        'id' => $id,
                        'position' => count($widgets_to_render) + 1,
                        'width' => (float)($def['width'] ?? 1.0),
                        'height' => (float)($def['height'] ?? 1.0)
                    ];
                }
                // Sort them alphabetically by ID for consistent positioning
                usort($widgets_to_render, function($a, $b) {
                    return strcmp($a['id'], $b['id']);
                });
            } else {
                // Otherwise, render the active widgets from persistent storage
                $widgets_to_render = $settings['active_widgets'];
            }

            foreach ($widgets_to_render as $index => $widget):
                $widget_id = $widget['id'];
                // Use a fallback for widget_def if config.php isn't correctly loading it
                $widget_def = $available_widgets[$widget_id] ?? ['name' => 'Unknown Widget', 'icon' => 'question', 'width' => 1.0, 'height' => 1.0];
                
                // Use the dimensions from the active_widgets array if present, otherwise fall back to config default
                // And ensure they are clamped for safety during rendering
                $current_width_user_facing = max(0.5, min(3.0, (float)($widget['width'] ?? $widget_def['width'])));
                $current_height_user_facing = max(0.5, min(4.0, (float)($widget['height'] ?? $widget_def['height'])));

                // Convert user-facing units to internal grid units (doubled for half-unit precision)
                $current_width_internal = $current_width_user_facing * 2;
                $current_height_internal = $current_height_user_facing * 2;
            ?>
            <!-- Widget container, made draggable for reordering -->
            <div class="widget"
                 draggable="true"
                 style="--width: <?= $current_width_internal ?>; --height: <?= $current_height_internal ?>;"
                 data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                 data-widget-index="<?= $index ?>"
                 data-current-width="<?= $current_width_user_facing ?>"
                 data-current-height="<?= $current_height_user_facing ?>">
                <!-- This placeholder div marks the widget's original position in the DOM -->
                <div class="widget-placeholder" data-original-parent-id="widget-container" data-original-index="<?= $index ?>"></div>

                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-<?= $widget_def['icon'] ?? 'cube' ?>"></i>
                        <span><?= $widget_def['name'] ?? 'Widget' ?></span>
                    </div>
                    <div class="widget-actions">
                        <!-- Add data attributes to identify actions -->
                        <div class="widget-action action-settings"
                            data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                            data-widget-index="<?= $index ?>"
                            data-current-width="<?= $current_width_user_facing ?>"
                            data-current-height="<?= $current_height_user_facing ?>"
                            title="Adjust widget dimensions">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="widget-action action-expand">
                            <i class="fas fa-expand"></i>
                        </div>
                        <div class="widget-action remove-widget
                            <?= $settings['show_all_available_widgets'] ? 'disabled' : '' ?>"
                            data-index="<?= $index ?>"
                            title="<?= $settings['show_all_available_widgets'] ? 'Remove disabled in "Show All Widgets" mode' : 'Remove widget' ?>">
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


    <!-- Settings Panel (Global Dashboard Settings) -->
    <div class="overlay" id="settings-overlay"></div>
    <div class="settings-panel" id="settings-panel">
        <div class="settings-header">
            <h2>Dashboard Settings</h2>
            <button class="btn" id="close-settings">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="global-settings-form" method="post" class="settings-form">
            <div class="settings-group">
                <h3 class="settings-title">General Settings</h3>

                <div class="form-group">
                    <label for="dashboard_title">Dashboard Title</label>
                    <input type="text" id="dashboard_title" name="dashboard_title"
                        class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
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

                <!-- NEW: Show All Available Widgets Toggle -->
                <div class="form-group">
                    <label>Show All Available Widgets</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="show_all_available_widgets" id="show_all_available_widgets"
                            <?= $settings['show_all_available_widgets'] ? 'checked' : '' ?>>
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

            <div class="settings-group">
                <h3 class="settings-title">Add Existing Widget</h3>

                <div class="form-group">
                    <label for="widget_select">Select Widget</label>
                    <select id="widget_select" name="widget_id" class="form-control">
                        <?php foreach ($available_widgets as $id => $widget): ?>
                        <option value="<?= $id ?>"><?= $widget['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="add_widget" class="btn btn-primary" id="add-widget-to-dashboard-btn" style="width: 100%;">
                    <i class="fas fa-plus"></i> Add Widget to Dashboard
                </button>
            </div>

            <!-- NEW: Create New Widget Template Button -->
            <div class="settings-group">
                <h3 class="settings-title">Create New Widget</h3>
                <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 15px;">
                    Generate a new blank widget file ready for your custom code.
                </p>
                <button type="button" class="btn btn-primary" id="open-create-widget-modal" style="width: 100%;">
                    <i class="fas fa-file-code"></i> Create New Widget Template
                </button>
            </div>


            <div class="settings-group">
                <h3 class="settings-title">Advanced</h3>
                <div class="form-group">
                    <label>Export Configuration</label>
                    <button class="btn" style="width: 100%;">
                        <i class="fas fa-download"></i> Download Settings
                    </button>
                </div>

                <div class="form-group">
                    <label>Import Configuration</label>
                    <input type="file" class="form-control">
                </div>
                <!-- NEW: Delete Settings JSON Button -->
                <div class="form-group">
                    <label>Reset Dashboard</label>
                    <button type="button" id="delete-settings-json-btn" class="btn btn-danger" style="width: 100%;">
                        <i class="fas fa-trash-alt"></i> Delete Settings JSON (Reset All)
                    </button>
                </div>
            </div>

            <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </form>
    </div>

    <script type="module" src="src/js/main.js"></script>
</body>
</html>
