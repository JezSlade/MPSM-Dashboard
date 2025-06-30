<?php
// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// --- Cache Control Headers ---
// These headers instruct the browser not to cache the page, ensuring
// it always fetches the latest content, especially after a POST request.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past
// --- End Cache Control Headers ---

// dashboard.php
session_start();

// Include configuration and helper functions
require_once 'config.php'; // Ensures DASHBOARD_SETTINGS_FILE is defined
require_once 'helpers.php';

// --- Persistent Settings & Widgets Functions ---

/**
 * Default dashboard settings including default active widgets.
 * This acts as a fallback if the settings file doesn't exist or is invalid.
 */
$default_dashboard_state = [
    'title' => 'Glass Dashboard',
    'accent_color' => '#6366f1',
    'glass_intensity' => 0.6,
    'blur_amount' => '10px',
    'enable_animations' => true,
    'show_all_available_widgets' => false,
    'active_widgets' => [
        // Default widgets with their initial default dimensions (from config.php)
        ['id' => 'stats', 'position' => 1],
        ['id' => 'tasks', 'position' => 2],
        ['id' => 'calendar', 'position' => 3],
        ['id' => 'notes', 'position' => 4],
        ['id' => 'activity', 'position' => 5]
    ]
];

/**
 * Loads dashboard settings and active widgets from the JSON file.
 *
 * @return array Loaded settings including active_widgets or default state.
 */
function loadDashboardState() {
    global $default_dashboard_state, $available_widgets; // Access global config

    $loaded_state = [];
    if (file_exists(DASHBOARD_SETTINGS_FILE)) {
        $json_data = file_get_contents(DASHBOARD_SETTINGS_FILE);
        $decoded_state = json_decode($json_data, true);
        if (is_array($decoded_state)) {
            $loaded_state = $decoded_state;
        }
    }

    // Merge loaded state with defaults to ensure all keys are present
    $final_state = array_replace_recursive($default_dashboard_state, $loaded_state);

    // --- NEW: Ensure active_widgets entries have width/height, falling back to config defaults ---
    if (isset($final_state['active_widgets']) && is_array($final_state['active_widgets'])) {
        foreach ($final_state['active_widgets'] as $key => $widget_entry) {
            $widget_id = $widget_entry['id'];
            // Get default dimensions from available_widgets (which are loaded from config.php)
            $default_width = $available_widgets[$widget_id]['width'] ?? 1;
            $default_height = $available_widgets[$widget_id]['height'] ?? 1;

            // Apply loaded dimensions, or fall back to defaults if not present in JSON
            $final_state['active_widgets'][$key]['width'] = $widget_entry['width'] ?? $default_width;
            $final_state['active_widgets'][$key]['height'] = $widget_entry['height'] ?? $default_height;
        }
    } else {
        // If active_widgets was missing or not an array, use default ones
        $final_state['active_widgets'] = $default_dashboard_state['active_widgets'];
    }
    // --- END NEW ---

    return $final_state;
}

/**
 * Saves the entire dashboard state (settings + active widgets) to the JSON file.
 *
 * @param array $state The complete dashboard state array to save.
 * @return bool True on success, false on failure.
 */
function saveDashboardState(array $state) {
    $json_data = json_encode($state, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        error_log("Failed to encode dashboard state to JSON: " . json_last_error_msg());
        return false;
    }
    // Attempt to write the file. File permissions are crucial here.
    $result = file_put_contents(DASHBOARD_SETTINGS_FILE, $json_data);
    if ($result === false) {
        $error_message = "Failed to write dashboard state to file: " . DASHBOARD_SETTINGS_FILE;
        if (!is_writable(dirname(DASHBOARD_SETTINGS_FILE))) {
             $error_message .= " - Directory not writable: " . dirname(DASHBOARD_SETTINGS_FILE);
        } else if (file_exists(DASHBOARD_SETTINGS_FILE) && !is_writable(DASHBOARD_SETTINGS_FILE)) {
             $error_message .= " - File exists but is not writable: " . DASHBOARD_SETTINGS_FILE;
        } else {
            $error_message .= " - Unknown write error."; // Generic error if no specific permission issue found
        }
        error_log($error_message);
    }
    return $result !== false;
}

// --- End Persistent Settings & Widgets Functions ---


// Load current dashboard state (settings + active widgets)
$current_dashboard_state = loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'active_widgets' with dimensions

// IMPORTANT: Initialize $_SESSION['active_widgets'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['active_widgets'] = $current_dashboard_state['active_widgets'];


// Handle POST requests for widget management and settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $has_state_changed = false; // Flag to know if we need to save the state

    // Check the 'action_type' to dispatch
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'add_widget' && !empty($_POST['widget_id'])) {
        // Only allow adding widgets if 'show all' is OFF
        if (!$settings['show_all_available_widgets']) {
            $widget_id_to_add = $_POST['widget_id'];
            // Get default dimensions from available_widgets
            $default_width = $available_widgets[$widget_id_to_add]['width'] ?? 1;
            $default_height = $available_widgets[$widget_id_to_add]['height'] ?? 1;

            $new_widget = [
                'id' => $widget_id_to_add,
                'position' => count($_SESSION['active_widgets']) + 1,
                'width' => $default_width,  // NEW: Add default width
                'height' => $default_height // NEW: Add default height
            ];
            $_SESSION['active_widgets'][] = $new_widget;
            $has_state_changed = true;
        }

    } elseif ($action_type === 'remove_widget' && isset($_POST['widget_index'])) {
        // Only allow removing widgets if 'show all' is OFF
        if (!$settings['show_all_available_widgets']) {
            $widget_index_to_remove = (int)$_POST['widget_index'];

            if (isset($_SESSION['active_widgets'][$widget_index_to_remove])) {
                unset($_SESSION['active_widgets'][$widget_index_to_remove]);
                $_SESSION['active_widgets'] = array_values($_SESSION['active_widgets']); // Re-index array
                $has_state_changed = true;
            }
        }

    } elseif ($action_type === 'update_settings') {
        // Update general dashboard settings
        $settings_from_post = [
            'title' => $_POST['dashboard_title'] ?? 'Glass Dashboard',
            'accent_color' => $_POST['accent_color'] ?? '#6366f1',
            'glass_intensity' => (float)($_POST['glass_intensity'] ?? 0.6),
            'blur_amount' => $_POST['blur_amount'] ?? '10px',
            'enable_animations' => isset($_POST['enable_animations']) && $_POST['enable_animations'] === '1',
            'show_all_available_widgets' => isset($_POST['show_all_available_widgets']) && $_POST['show_all_available_widgets'] === '1'
        ];
        
        // Update current $settings array with new values from POST
        $settings = array_merge($settings, $settings_from_post);
        $_SESSION['dashboard_settings'] = $settings_from_post; // Update session for current request
        $has_state_changed = true;

        // Special handling if 'show_all_available_widgets' was just turned ON
        if ($settings['show_all_available_widgets'] && !($current_dashboard_state['show_all_available_widgets'] ?? false)) {
            // Overwrite active_widgets with all available widgets, sorted alphabetically by ID,
            // using their default dimensions from config.php
            $new_active_widgets = [];
            foreach ($available_widgets as $id => $def) {
                $new_active_widgets[] = [
                    'id' => $id,
                    'position' => count($new_active_widgets) + 1,
                    'width' => $def['width'] ?? 1,   // NEW: Use default width from config
                    'height' => $def['height'] ?? 1  // NEW: Use default height from config
                ];
            }
            usort($new_active_widgets, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });
            $_SESSION['active_widgets'] = $new_active_widgets;
        }
        // If 'show_all_available_widgets' was just turned OFF, active_widgets in session
        // will automatically revert to the last saved persistent state due to loadDashboardState at top.

    } elseif ($action_type === 'update_widget_dimensions' && isset($_POST['widget_index']) && isset($_POST['new_width']) && isset($_POST['new_height'])) {
        $widget_index = (int)$_POST['widget_index'];
        $new_width = (int)$_POST['new_width'];
        $new_height = (int)$_POST['new_height'];

        // Only allow changing dimensions if 'show all' is OFF
        if (!$settings['show_all_available_widgets']) {
            if (isset($_SESSION['active_widgets'][$widget_index])) {
                $_SESSION['active_widgets'][$widget_index]['width'] = $new_width;
                $_SESSION['active_widgets'][$widget_index]['height'] = $new_height;
                $has_state_changed = true;
            }
        }
    }
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by POST or loaded from persistence.
$settings = array_replace_recursive($default_dashboard_state, $current_dashboard_state, $_SESSION['dashboard_settings'] ?? []);
$settings['active_widgets'] = $_SESSION['active_widgets']; // Make sure active_widgets is the latest from session

// Pass available widgets to the view
global $available_widgets;

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

    <!-- NEW: Widget Settings Modal Structure -->
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
                        <input type="number" id="widget-settings-width" name="new_width" min="1" max="4" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="widget-settings-height">Height (Grid Units)</label>
                        <input type="number" id="widget-settings-height" name="new_height" min="1" max="4" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Save Dimensions</button>
                </form>
            </div>
        </div>
    </div>
    <!-- END NEW: Widget Settings Modal Structure -->

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
                <button class="btn btn-primary" id="new-widget-btn">
                    <i class="fas fa-plus"></i> New Widget
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
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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
                        'width' => $def['width'] ?? 1,
                        'height' => $def['height'] ?? 1
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
                $widget_def = $available_widgets[$widget_id] ?? ['width' => 1, 'height' => 1];
                // Use the dimensions from the active_widgets array if present, otherwise fall back to config default
                $current_width = $widget['width'] ?? $widget_def['width'];
                $current_height = $widget['height'] ?? $widget_def['height'];
            ?>
            <div class="widget"
                 style="--width: <?= $current_width ?>; --height: <?= $current_height ?>"
                 data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                 data-widget-index="<?= $index ?>"
                 data-current-width="<?= $current_width ?>"
                 data-current-height="<?= $current_height ?>">
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
                            data-current-width="<?= $current_width ?>"
                            data-current-height="<?= $current_height ?>"
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

        <form method="post" class="settings-form">
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
                    <label for="blur_amount">Blur Amount</label>
                    <select id="blur_amount" name="blur_amount" class="form-control">
                        <option value="5px" <?= $settings['blur_amount'] == '5px' ? 'selected' : '' ?>>Subtle (5px)</option>
                        <option value="10px" <?= $settings['blur_amount'] == '10px' ? 'selected' : '' ?>>Standard (10px)</option>
                        <option value="15px" <?= $settings['blur_amount'] == '15px' ? 'selected' : '' ?>>Strong (15px)</option>
                        <option value="20px" <?= $settings['blur_amount'] == '20px' ? 'selected' : '' ?>>Extra Strong (20px)</option>
                    </select>
                </div>
            </div>

            <div class="settings-group">
                <h3 class="settings-title">Add New Widget</h3>

                <div class="form-group">
                    <label for="widget_select">Select Widget</label>
                    <select id="widget_select" name="widget_id" class="form-control">
                        <?php foreach ($available_widgets as $id => $widget): ?>
                        <option value="<?= $id ?>"><?= $widget['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="add_widget" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Add Widget to Dashboard
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
            </div>

            <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </form>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>
