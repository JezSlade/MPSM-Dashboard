<?php
// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

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
    'active_widgets' => [
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
    global $default_dashboard_state; // Access the default state
    if (file_exists(DASHBOARD_SETTINGS_FILE)) {
        $json_data = file_get_contents(DASHBOARD_SETTINGS_FILE);
        $loaded_state = json_decode($json_data, true);

        // Merge loaded state with defaults to ensure all keys are present
        // This handles cases where new settings keys are added in future versions
        if (is_array($loaded_state)) {
            // Ensure active_widgets is an array, if not, use default
            if (!isset($loaded_state['active_widgets']) || !is_array($loaded_state['active_widgets'])) {
                $loaded_state['active_widgets'] = $default_dashboard_state['active_widgets'];
            }
            return array_replace_recursive($default_dashboard_state, $loaded_state);
        }
    }
    return $default_dashboard_state; // Return default if file not found or invalid
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
        echo "<p style='color: red;'>PHP Error: Failed to encode JSON for saving. Details in server error log.</p>";
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
        echo "<p style='color: red;'>PHP Critical Error: " . htmlspecialchars($error_message) . "</p>";
    }
    return $result !== false;
}

// --- End Persistent Settings & Widgets Functions ---


// Load current dashboard state (settings + active widgets)
$current_dashboard_state = loadDashboardState();
$settings = $current_dashboard_state; // Settings now includes active_widgets

// IMPORTANT: Initialize $_SESSION['active_widgets'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['active_widgets'] = $current_dashboard_state['active_widgets'];


// --- DEBUGGING OUTPUT: Session state BEFORE POST handling ---
echo '<h2>Debugging Active Widgets & Settings Persistence</h2>';
echo '<h3>SESSION Active Widgets (BEFORE POST):</h3>';
echo '<pre>';
var_dump($_SESSION['active_widgets']);
echo '</pre>';

echo '<h3>Persistent Settings (BEFORE POST Load):</h3>';
echo '<pre>';
if (file_exists(DASHBOARD_SETTINGS_FILE)) {
    echo htmlspecialchars(file_get_contents(DASHBOARD_SETTINGS_FILE));
} else {
    echo "dashboard_settings.json does not exist.";
}
echo '</pre>';
// --- END DEBUGGING OUTPUT ---


// Handle POST requests for widget management and settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $has_state_changed = false; // Flag to know if we need to save the state

    if (isset($_POST['add_widget']) && !empty($_POST['widget_id'])) {
        // Add new widget to active_widgets in session
        $new_widget = [
            'id' => $_POST['widget_id'],
            'position' => count($_SESSION['active_widgets']) + 1
        ];
        $_SESSION['active_widgets'][] = $new_widget;
        $has_state_changed = true;
        echo "<p style='color: green;'>PHP Action: Widget '{$_POST['widget_id']}' added to session.</p>";

    } elseif (isset($_POST['remove_widget']) && isset($_POST['widget_index'])) {
        // Remove widget from active_widgets in session
        $widget_index_to_remove = (int)$_POST['widget_index'];
        echo "<p style='color: orange;'>PHP Action: Attempting to remove widget at index {$widget_index_to_remove}.</p>";

        if (isset($_SESSION['active_widgets'][$widget_index_to_remove])) {
            unset($_SESSION['active_widgets'][$widget_index_to_remove]);
            $_SESSION['active_widgets'] = array_values($_SESSION['active_widgets']); // Re-index array
            $has_state_changed = true;
            echo "<p style='color: green;'>PHP Action: Widget at index {$widget_index_to_remove} successfully unset and array re-indexed in session.</p>";
        } else {
            echo "<p style='color: red;'>PHP Warning: Widget at index {$widget_index_to_remove} not found in session.</p>";
        }
    } elseif (isset($_POST['update_settings'])) {
        // Update general dashboard settings
        $settings_from_post = [
            'title' => $_POST['dashboard_title'] ?? 'Glass Dashboard',
            'accent_color' => $_POST['accent_color'] ?? '#6366f1',
            'glass_intensity' => (float)($_POST['glass_intensity'] ?? 0.6),
            'blur_amount' => $_POST['blur_amount'] ?? '10px',
            'enable_animations' => isset($_POST['enable_animations'])
        ];
        // Merge only the general settings part
        $settings = array_merge($settings, $settings_from_post); // Update current $settings array
        $_SESSION['dashboard_settings'] = $settings_from_post; // Update session for current request
        $has_state_changed = true;
        echo "<p style='color: green;'>PHP Action: General settings updated in session.</p>";
    }

    // If any state (settings or active widgets) changed, save the entire state
    if ($has_state_changed) {
        // Create the full state array to save, combining current $settings and active widgets from session
        $state_to_save = $settings; // Start with current $settings
        $state_to_save['active_widgets'] = $_SESSION['active_widgets']; // OVERRIDE active_widgets with latest session state

        echo '<h3>STATE TO BE SAVED:</h3><pre>';
        var_dump($state_to_save);
        echo '</pre>';

        if (saveDashboardState($state_to_save)) {
            echo "<p style='color: green;'>PHP Persistence: Dashboard state successfully saved to JSON file.</p>";
        } else {
            echo "<p style='color: red;'>PHP Persistence Critical Error: Failed to save dashboard state persistently. Check server error logs for more details!</p>";
        }
    }
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by POST or loaded from persistence.
// This merge ensures default values are applied, then persistent ones, then session ones.
$settings = array_replace_recursive($default_dashboard_state, $current_dashboard_state, $_SESSION['dashboard_settings'] ?? []);
// The 'active_widgets' for rendering should always come from the final $_SESSION state after processing
$settings['active_widgets'] = $_SESSION['active_widgets'];


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
            <?php foreach ($settings['active_widgets'] as $index => $widget): // Loop through persistent active_widgets
                $widget_def = $available_widgets[$widget['id']] ?? ['width' => 1, 'height' => 1];
            ?>
            <div class="widget" style="--width: <?= $widget_def['width'] ?>; --height: <?= $widget_def['height'] ?>">
                <!-- This placeholder div marks the widget's original position in the DOM -->
                <div class="widget-placeholder" data-original-parent-id="widget-container" data-original-index="<?= $index ?>"></div>

                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-<?= $widget_def['icon'] ?? 'cube' ?>"></i>
                        <span><?= $widget_def['name'] ?? 'Widget' ?></span>
                    </div>
                    <div class="widget-actions">
                        <!-- Add data attributes to identify actions -->
                        <div class="widget-action action-settings" data-widget-id="<?= $widget['id'] ?>">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="widget-action action-expand">
                            <i class="fas fa-expand"></i>
                        </div>
                        <div class="widget-action remove-widget" data-index="<?= $index ?>">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <?= render_widget($widget['id']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Simple Message Modal Structure -->
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


    <!-- Settings Panel -->
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
