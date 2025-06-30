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

// Define the application root directory for security
define('APP_ROOT', __DIR__);

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

    // --- LOGGING: Before adjusting active_widgets from loaded_state ---
    error_log("DEBUG: loadDashboardState - After initial merge with defaults:");
    error_log("DEBUG: " . print_r($final_state, true));

    // --- Ensure active_widgets entries have width/height, falling back to config defaults ---
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

    // --- LOGGING: After active_widgets dimension enrichment ---
    error_log("DEBUG: loadDashboardState - Final state after dimension enrichment:");
    error_log("DEBUG: " . print_r($final_state, true));

    return $final_state;
}

/**
 * Saves the entire dashboard state (settings + active widgets) to the JSON file.
 *
 * @param array $state The complete dashboard state array to save.
 * @return bool True on success, false on failure.
 */
function saveDashboardState(array $state) {
    // --- LOGGING: State being saved ---
    error_log("DEBUG: saveDashboardState - Attempting to save the following state:");
    error_log("DEBUG: " . print_r($state, true));

    $json_data = json_encode($state, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        error_log("ERROR: saveDashboardState - Failed to encode dashboard state to JSON: " . json_last_error_msg());
        return false;
    }
    // Attempt to write the file. File permissions are crucial here.
    $result = file_put_contents(DASHBOARD_SETTINGS_FILE, $json_data);
    if ($result === false) {
        $error_message = "ERROR: saveDashboardState - Failed to write dashboard state to file: " . DASHBOARD_SETTINGS_FILE;
        if (!is_writable(dirname(DASHBOARD_SETTINGS_FILE))) {
             $error_message .= " - Directory not writable: " . dirname(DASHBOARD_SETTINGS_FILE);
        } else if (file_exists(DASHBOARD_SETTINGS_FILE) && !is_writable(DASHBOARD_SETTINGS_FILE)) {
             $error_message .= " - File exists but is not writable: " . DASHBOARD_SETTINGS_FILE;
        } else {
            $error_message .= " - Unknown write error."; // Generic error if no specific permission issue found
        }
        error_log($error_message);
    } else {
        error_log("DEBUG: saveDashboardState - Successfully saved state to " . DASHBOARD_SETTINGS_FILE);
    }
    return $result !== false;
}

// --- END Persistent Settings & Widgets Functions ---

// --- IDE Widget File Operations (Server-Side) ---

/**
 * Validates and normalizes a given file path to prevent directory traversal.
 * Ensures the path stays within the APP_ROOT.
 *
 * @param string $path The user-provided path.
 * @return string|false The normalized real path within APP_ROOT, or false if invalid/outside root.
 */
function validate_path($path) {
    $full_path = realpath(APP_ROOT . '/' . $path);

    // Ensure the path is within the APP_ROOT and is not pointing to a device/symlink outside.
    if ($full_path && str_starts_with($full_path, APP_ROOT . DIRECTORY_SEPARATOR)) {
        return $full_path;
    }
    // Handle the APP_ROOT itself (e.g., if path is '.' or '')
    if ($full_path === APP_ROOT) {
        return $full_path;
    }

    return false; // Path is invalid or outside APP_ROOT
}


/**
 * Lists files and directories within a given path, restricted to APP_ROOT.
 * @param string $path Relative path from APP_ROOT.
 * @return array|false List of files/dirs (name, type), or false on error/invalid path.
 */
function list_files($path) {
    $absolute_path = validate_path($path);
    if ($absolute_path === false || !is_dir($absolute_path)) {
        error_log("IDE: list_files - Invalid or non-directory path: " . $path);
        return false;
    }

    $items = scandir($absolute_path);
    if ($items === false) {
        error_log("IDE: list_files - Failed to scan directory: " . $absolute_path);
        return false;
    }

    $file_list = [];
    foreach ($items as $item) {
        if ($item === '.' || ($item === '..' && $absolute_path === APP_ROOT)) {
            // '.' is always useful. '..' only if not at root.
            continue;
        }

        $item_full_path = $absolute_path . DIRECTORY_SEPARATOR . $item;
        $relative_path = str_replace(APP_ROOT . DIRECTORY_SEPARATOR, '', $item_full_path);

        $file_list[] = [
            'name' => $item,
            'path' => $relative_path,
            'type' => is_dir($item_full_path) ? 'dir' : 'file',
            'is_writable' => is_writable($item_full_path)
        ];
    }

    // Sort directories first, then files, both alphabetically
    usort($file_list, function($a, $b) {
        if ($a['type'] === 'dir' && $b['type'] === 'file') return -1;
        if ($a['type'] === 'file' && $b['type'] === 'dir') return 1;
        return strcmp($a['name'], $b['name']);
    });

    // Add '..' entry if not at the root
    if ($absolute_path !== APP_ROOT) {
        $parent_path_relative = str_replace(APP_ROOT . DIRECTORY_SEPARATOR, '', dirname($absolute_path));
        // Special case for root-level folders: if parent path becomes just '.' after stripping APP_ROOT, make it '' for consistency.
        if ($parent_path_relative === '.') $parent_path_relative = '';

        array_unshift($file_list, [
            'name' => '..',
            'path' => $parent_path_relative,
            'type' => 'dir',
            'is_writable' => true // Parent is always conceptually writable to navigate back
        ]);
    }
    
    return $file_list;
}

/**
 * Reads content of a file, restricted to APP_ROOT.
 * @param string $path Relative path from APP_ROOT.
 * @return string|false File content, or false on error/invalid path.
 */
function read_file($path) {
    $absolute_path = validate_path($path);
    if ($absolute_path === false || !is_file($absolute_path)) {
        error_log("IDE: read_file - Invalid or non-file path: " . $path);
        return false;
    }
    $content = file_get_contents($absolute_path);
    if ($content === false) {
        error_log("IDE: read_file - Failed to read file: " . $absolute_path);
    }
    return $content;
}

/**
 * Saves content to a file, restricted to APP_ROOT.
 * @param string $path Relative path from APP_ROOT.
 * @param string $content Content to write.
 * @return bool True on success, false on failure.
 */
function save_file($path, $content) {
    $absolute_path = validate_path($path);
    if ($absolute_path === false) {
        error_log("IDE: save_file - Invalid path: " . $path);
        return false;
    }
    // Check if the file exists and is writable, or if its directory is writable for new files
    if (file_exists($absolute_path) && !is_writable($absolute_path)) {
        error_log("IDE: save_file - File exists but not writable: " . $absolute_path);
        return false;
    }
    if (!file_exists($absolute_path) && !is_writable(dirname($absolute_path))) {
        error_log("IDE: save_file - Directory not writable for new file: " . dirname($absolute_path));
        return false;
    }

    $result = file_put_contents($absolute_path, $content);
    if ($result === false) {
        error_log("IDE: save_file - Failed to write content to file: " . $absolute_path);
    }
    return $result !== false;
}

// --- END IDE Widget File Operations ---


// Check if this is an AJAX request
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($is_ajax_request) {
    // Handle AJAX requests
    $ajax_action = $_POST['ajax_action'] ?? '';
    $response = ['status' => 'error', 'message' => 'Unknown AJAX action.'];

    error_log("DEBUG: AJAX request detected. Action: " . $ajax_action);
    error_log("DEBUG: AJAX POST data: " . print_r($_POST, true));

    switch ($ajax_action) {
        case 'ide_list_files':
            $current_dir = $_POST['path'] ?? '.';
            $files = list_files($current_dir);
            if ($files !== false) {
                $response = ['status' => 'success', 'files' => $files, 'current_path' => ($current_dir === '.' ? '' : $current_dir)];
            } else {
                $response['message'] = "Failed to list files or invalid path.";
            }
            break;
        case 'ide_read_file':
            $file_path = $_POST['path'] ?? '';
            $content = read_file($file_path);
            if ($content !== false) {
                $response = ['status' => 'success', 'content' => $content];
            } else {
                $response['message'] = "Failed to read file or invalid path.";
            }
            break;
        case 'ide_save_file':
            $file_path = $_POST['path'] ?? '';
            $content = $_POST['content'] ?? '';
            if (save_file($file_path, $content)) {
                $response = ['status' => 'success', 'message' => 'File saved successfully.'];
            } else {
                $response['message'] = "Failed to save file. Check permissions or path.";
            }
            break;
        case 'delete_settings_json': // NEW: Handle deletion of settings file
            if (file_exists(DASHBOARD_SETTINGS_FILE)) {
                if (unlink(DASHBOARD_SETTINGS_FILE)) {
                    // Clear session to ensure default state is loaded on refresh
                    session_destroy();
                    session_start(); // Start a new session
                    $response = ['status' => 'success', 'message' => 'Dashboard settings reset successfully.'];
                    error_log("DEBUG: dashboard_settings.json deleted successfully.");
                } else {
                    $response['message'] = "Failed to delete settings file. Check permissions.";
                    error_log("ERROR: Failed to unlink dashboard_settings.json. Path: " . DASHBOARD_SETTINGS_FILE);
                }
            } else {
                $response['message'] = "Settings file does not exist.";
                error_log("INFO: Attempted to delete dashboard_settings.json, but it didn't exist.");
            }
            break;
        default:
            // Handled by default response
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Terminate script execution after sending JSON response for AJAX
}


// --- Normal Full Page POST Request Handling (only if not AJAX) ---
// This block will only execute if it's not an AJAX request.
// It handles widget adds/removes, global settings updates, and widget dimension updates (which still trigger full reloads).

// Load current dashboard state (settings + active widgets)
$current_dashboard_state = loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'active_widgets' with dimensions

// IMPORTANT: Initialize $_SESSION['active_widgets'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['active_widgets'] = $current_dashboard_state['active_widgets'];

// --- LOGGING: $_SESSION active_widgets after initial load ---
error_log("DEBUG: index.php - Initial \$_SESSION['active_widgets'] after loadDashboardState:");
error_log("DEBUG: " . print_r($_SESSION['active_widgets'], true));


// Handle POST requests for widget management and settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // This will only be true for non-AJAX POSTs now
    $has_state_changed = false; // Flag to know if we need to save the state

    // Check the 'action_type' to dispatch
    $action_type = $_POST['action_type'] ?? '';

    // --- LOGGING: Incoming POST action and data ---
    error_log("DEBUG: index.php (FULL REFRESH) - Received POST action_type: " . $action_type);
    error_log("DEBUG: index.php (FULL REFRESH) - POST data: " . print_r($_POST, true));


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
                'width' => $default_width,
                'height' => $default_height
            ];
            $_SESSION['active_widgets'][] = $new_widget;
            $has_state_changed = true;
            error_log("DEBUG: index.php - Widget added to session: " . $widget_id_to_add);
        } else {
            error_log("INFO: index.php - Add widget attempted but 'Show All Widgets' mode is active.");
        }

    } elseif ($action_type === 'remove_widget' && isset($_POST['widget_index'])) {
        // Only allow removing widgets if 'show all' is OFF
        if (!$settings['show_all_available_widgets']) {
            $widget_index_to_remove = (int)$_POST['widget_index'];

            if (isset($_SESSION['active_widgets'][$widget_index_to_remove])) {
                $removed_id = $_SESSION['active_widgets'][$widget_index_to_remove]['id'];
                unset($_SESSION['active_widgets'][$widget_index_to_remove]);
                $_SESSION['active_widgets'] = array_values($_SESSION['active_widgets']); // Re-index array
                $has_state_changed = true;
                error_log("DEBUG: index.php - Widget removed from session: " . $removed_id . " at index " . $widget_index_to_remove);
            } else {
                error_log("WARN: index.php - Attempted to remove non-existent widget at index: " . $widget_index_to_remove);
            }
        } else {
            error_log("INFO: index.php - Remove widget attempted but 'Show All Widgets' mode is active.");
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
        error_log("DEBUG: index.php - Global settings updated in session.");


        // Special handling if 'show_all_available_widgets' was just turned ON
        if ($settings['show_all_available_widgets'] && !($current_dashboard_state['show_all_available_widgets'] ?? false)) {
            // Overwrite active_widgets with all available widgets, sorted alphabetically by ID,
            // using their default dimensions from config.php
            $new_active_widgets = [];
            foreach ($available_widgets as $id => $def) {
                $new_active_widgets[] = [
                    'id' => $id,
                    'position' => count($new_active_widgets) + 1,
                    'width' => $def['width'] ?? 1,
                    'height' => $def['height'] ?? 1
                ];
            }
            usort($new_active_widgets, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });
            $_SESSION['active_widgets'] = $new_active_widgets;
            error_log("DEBUG: index.php - 'Show All Widgets' turned ON. Active widgets reset to all available.");
        }

    } elseif ($action_type === 'update_widget_dimensions' && isset($_POST['widget_index']) && isset($_POST['new_width']) && isset($_POST['new_height'])) {
        $widget_index = (int)$_POST['widget_index'];
        $new_width = (int)$_POST['new_width'];
        $new_height = (int)$_POST['new_height'];

        // Ensure width/height are within the allowed bounds (width max 3, height max 4)
        $new_width = max(1, min(3, $new_width)); // Clamp between 1 and 3
        $new_height = max(1, min(4, $new_height)); // Clamp between 1 and 4 (height can still be 4)


        // Only allow changing dimensions if 'show all' is OFF
        if (!$settings['show_all_available_widgets']) {
            if (isset($_SESSION['active_widgets'][$widget_index])) {
                $_SESSION['active_widgets'][$widget_index]['width'] = $new_width;
                $_SESSION['active_widgets'][$widget_index]['height'] = $new_height;
                $has_state_changed = true;
                error_log("DEBUG: index.php - Widget dimensions updated for index " . $widget_index . ": W=" . $new_width . ", H=" . $new_height);
            } else {
                error_log("WARN: index.php - Attempted to update dimensions for non-existent widget at index: " . $widget_index);
            }
        } else {
            error_log("INFO: index.php - Widget dimension update attempted but 'Show All Widgets' mode is active.");
        }
    } else {
        error_log("WARN: index.php (FULL REFRESH) - Unknown or invalid POST action_type received: " . ($_POST['action_type'] ?? 'EMPTY'));
    }

    // If any state (settings or active widgets) changed, save the entire state
    if ($has_state_changed) {
        // Create the full state array to save, combining current $settings and active widgets from session
        $state_to_save = $settings; // Start with current $settings
        // The 'active_widgets' in $state_to_save must always come from $_SESSION after processing POST
        $state_to_save['active_widgets'] = $_SESSION['active_widgets'];

        if (!saveDashboardState($state_to_save)) {
            error_log("CRITICAL ERROR: index.php - Failed to save dashboard state persistently. Check server error logs for more details!");
        }
    }
    // --- LOGGING: $_SESSION active_widgets after POST processing ---
    error_log("DEBUG: index.php - \$_SESSION['active_widgets'] after POST processing:");
    error_log("DEBUG: " . print_r($_SESSION['active_widgets'], true));

}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by POST or loaded from persistence.
// This merge ensures default values are applied, then persistent ones, then session ones.
$settings = array_replace_recursive($default_dashboard_state, $current_dashboard_state, $_SESSION['dashboard_settings'] ?? []);
// The 'active_widgets' for rendering should always come from the final $_SESSION state after processing
$settings['active_widgets'] = $_SESSION['active_widgets'];

// --- LOGGING: Final $settings['active_widgets'] before HTML rendering ---
error_log("DEBUG: index.php - Final \$settings['active_widgets'] before HTML rendering:");
error_log("DEBUG: " . print_r($settings['active_widgets'], true));


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
                        <input type="number" id="widget-settings-width" name="new_width" min="1" max="3" class="form-control">
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

    <script src="dashboard.js"></script>
</body>
</html>
