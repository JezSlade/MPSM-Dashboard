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
// NOTE: These files are assumed to be present and correctly structured.
// If you are still seeing "Unknown Widget" or "Widget content not found",
// it means your config.php and helpers.php (and individual widget files)
// are NOT the correct versions. This reordering feature will work on the
// *containers*, but cannot fix the content if PHP isn't generating it.
require_once 'config.php';
require_once 'helpers.php';

// Define the application root directory for security
define('APP_ROOT', __DIR__);
// Removed: define('DYNAMIC_WIDGETS_FILE', APP_ROOT . '/dynamic_widgets.json'); // This is now defined only in config.php

// --- Persistent Settings & Widgets Functions ---

/**
 * Default dashboard settings including default active widgets.
 * This acts as a fallback if the settings file doesn't exist or is invalid.
 */
$default_dashboard_state = [
    'title' => 'Glass Dashboard',
    'header_icon' => 'fas fa-gem', // New: Default header icon
    'accent_color' => '#6366f1',
    'glass_intensity' => 0.6,
    'blur_amount' => '10px',
    'enable_animations' => true,
    'show_all_available_widgets' => false,
    'active_widgets' => [
        // Default widgets with their initial default dimensions (from config.php)
        // These are examples. The actual width/height will be derived from config.php's $available_widgets
        // when a new widget is added or when show_all_available_widgets is enabled.
        ['id' => 'stats', 'position' => 1, 'width' => 2.0, 'height' => 1.0],
        ['id' => 'tasks', 'position' => 2, 'width' => 1.0, 'height' => 2.0],
        ['id' => 'calendar', 'position' => 3, 'width' => 1.0, 'height' => 1.0],
        ['id' => 'notes', 'position' => 4, 'width' => 1.0, 'height' => 1.0],
        ['id' => 'activity', 'position' => 5, 'width' => 2.0, 'height' => 1.0],
        ['id' => 'debug_info', 'position' => 6, 'width' => 2.0, 'height' => 2.0], // Added debug_info default
        ['id' => 'ide', 'position' => 7, 'width' => 3.0, 'height' => 3.0] // Added IDE default
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

    // --- Ensure active_widgets entries have width/height, falling back to config defaults ---
    // This is crucial to ensure dimensions are always set and valid.
    if (isset($final_state['active_widgets']) && is_array($final_state['active_widgets'])) {
        foreach ($final_state['active_widgets'] as $key => $widget_entry) {
            $widget_id = $widget_entry['id'];
            // Get default dimensions from available_widgets (which are loaded from config.php)
            $default_width = (float)($available_widgets[$widget_id]['width'] ?? 1.0);
            $default_height = (float)($available_widgets[$widget_id]['height'] ?? 1.0);

            // Apply loaded dimensions, or fall back to defaults if not present in JSON
            // Also clamp values to allowed min/max (0.5 to 3.0 for width, 0.5 to 4.0 for height)
            $final_state['active_widgets'][$key]['width'] = max(0.5, min(3.0, (float)($widget_entry['width'] ?? $default_width)));
            $final_state['active_widgets'][$key]['height'] = max(0.5, min(4.0, (float)($widget_entry['height'] ?? $default_height)));
        }
    } else {
        // If active_widgets was missing or not an array, use default ones
        $final_state['active_widgets'] = $default_dashboard_state['active_widgets'];
    }

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
    }
    return $result !== false;
}

/**
 * Loads dynamically created widget configurations from dynamic_widgets.json.
 * @return array An associative array of dynamic widget configurations.
 */
function loadDynamicWidgets() {
    if (file_exists(DYNAMIC_WIDGETS_FILE)) {
        $json_data = file_get_contents(DYNAMIC_WIDGETS_FILE);
        $widgets = json_decode($json_data, true);
        return is_array($widgets) ? $widgets : [];
    }
    return [];
}

/**
 * Saves dynamically created widget configurations to dynamic_widgets.json.
 * @param array $widgets An associative array of dynamic widget configurations.
 * @return bool True on success, false on failure.
 */
function saveDynamicWidgets(array $widgets) {
    $json_data = json_encode($widgets, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        error_log("ERROR: saveDynamicWidgets - Failed to encode dynamic widgets to JSON: " . json_last_error_msg());
        return false;
    }
    $result = file_put_contents(DYNAMIC_WIDGETS_FILE, $json_data);
    if ($result === false) {
        error_log("ERROR: saveDynamicWidgets - Failed to write dynamic widgets to file: " . DYNAMIC_WIDGETS_FILE);
    }
    return $result !== false;
}


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
        case 'delete_settings_json': // Handle deletion of settings file
            if (file_exists(DASHBOARD_SETTINGS_FILE)) {
                if (unlink(DASHBOARD_SETTINGS_FILE)) {
                    // Clear session to ensure default state is loaded on refresh
                    session_destroy();
                    session_start(); // Start a new session
                    $response = ['status' => 'success', 'message' => 'Dashboard settings reset successfully.'];
                } else {
                    $response['message'] = "Failed to delete settings file. Check permissions.";
                }
            } else {
                $response = ['status' => 'success', 'message' => 'Settings file already absent.'];
            }
            break;
        case 'add_widget':
            $widget_id = $_POST['widget_id'] ?? '';
            if (!empty($widget_id) && isset($available_widgets[$widget_id])) {
                $current_dashboard_state = loadDashboardState();
                $widget_exists = false;
                foreach ($current_dashboard_state['active_widgets'] as $active_widget) {
                    if ($active_widget['id'] === $widget_id) {
                        $widget_exists = true;
                        break;
                    }
                }

                if (!$widget_exists) {
                    // Add to active widgets with default dimensions from config.php
                    $new_widget_entry = [
                        'id' => $widget_id,
                        'position' => count($current_dashboard_state['active_widgets']) + 1, // Simple position increment
                        'width' => (float)($available_widgets[$widget_id]['width'] ?? 1.0),
                        'height' => (float)($available_widgets[$widget_id]['height'] ?? 1.0)
                    ];
                    $current_dashboard_state['active_widgets'][] = $new_widget_entry;
                    if (saveDashboardState($current_dashboard_state)) {
                        $response = ['status' => 'success', 'message' => htmlspecialchars($available_widgets[$widget_id]['name']) . ' widget added to active list.'];
                    } else {
                        $response['message'] = 'Failed to add widget to active list (save error).';
                    }
                } else {
                    $response['message'] = htmlspecialchars($available_widgets[$widget_id]['name']) . ' widget is already active.';
                }
            } else {
                $response['message'] = 'Invalid widget ID.';
            }
            break;
        case 'remove_widget':
            $widget_id = $_POST['widget_id'] ?? '';
            $current_dashboard_state = loadDashboardState();
            $initial_count = count($current_dashboard_state['active_widgets']);
            $current_dashboard_state['active_widgets'] = array_values(array_filter(
                $current_dashboard_state['active_widgets'],
                function($widget) use ($widget_id) {
                    return $widget['id'] !== $widget_id;
                }
            ));

            if (count($current_dashboard_state['active_widgets']) < $initial_count) {
                // Re-index positions after removal
                foreach ($current_dashboard_state['active_widgets'] as $key => &$widget) {
                    $widget['position'] = $key + 1;
                }
                if (saveDashboardState($current_dashboard_state)) {
                    $response = ['status' => 'success', 'message' => 'Widget removed successfully.'];
                } else {
                    $response['message'] = 'Failed to remove widget (save error).';
                }
            } else {
                $response['message'] = 'Widget not found in active list.';
            }
            break;
        case 'create_new_widget_template': // NEW: AJAX to create a new widget file
            $widget_name = trim($_POST['name'] ?? '');
            $widget_id = trim($_POST['id'] ?? '');
            $widget_icon = trim($_POST['icon'] ?? 'cube');
            $widget_width = (float)($_POST['width'] ?? 1.0); // Cast to float
            $widget_height = (float)($_POST['height'] ?? 1.0); // Cast to float

            // Basic validation
            if (empty($widget_name) || empty($widget_id)) {
                $response['message'] = 'Widget Name and ID are required.';
                break;
            }
            if (!preg_match('/^[a-z0-9_]+$/', $widget_id)) {
                $response['message'] = 'Widget ID can only contain lowercase letters, numbers, and underscores.';
                break;
            }

            $widget_file_path = APP_ROOT . '/widgets/' . $widget_id . '.php';
            if (file_exists($widget_file_path)) {
                $response['message'] = 'A widget with this ID already exists. Please choose a different ID.';
                break;
            }
            
            // Create the widget PHP file content
            $widget_template_content = "<?php\n" .
                "// widgets/{$widget_id}.php\n" .
                "// This widget was dynamically created. Feel free to modify its content.\n" .
                "?>\n" .
                "<div class=\"widget-content\">\n" .
                "    <h3><i class=\"<?= htmlspecialchars(\$widget_icon ?? 'fas fa-cube')?>\"></i> <?= htmlspecialchars(\$widget_name ?? '{$widget_name}') ?></h3>\n" .
                "    <p>This is your new custom widget: <strong><?= htmlspecialchars(\$widget_name ?? '{$widget_name}') ?></strong> (ID: {$widget_id}).</p>\n" .
                "    <p>You can edit this file at <code>widgets/{$widget_id}.php</code> to add your desired functionality.</p>\n" .
                "    <p>Default dimensions: Width <?= htmlspecialchars(\$widget_width ?? '{$widget_width}') ?>, Height <?= htmlspecialchars(\$widget_height ?? '{$widget_height}') ?></p>\n" .
                "</div>\n";

            if (file_put_contents($widget_file_path, $widget_template_content)) {
                // Add to dynamic widgets config
                $dynamic_widgets = loadDynamicWidgets();
                $dynamic_widgets[$widget_id] = [
                    'name' => $widget_name,
                    'icon' => $widget_icon,
                    'width' => $widget_width,
                    'height' => $widget_height,
                    'dynamic' => true // Mark as dynamically created
                ];
                if (saveDynamicWidgets($dynamic_widgets)) {
                    // Try to add it to the active dashboard state immediately
                    $current_dashboard_state = loadDashboardState();
                    $new_widget_entry = [
                        'id' => $widget_id,
                        'position' => count($current_dashboard_state['active_widgets']) + 1,
                        'width' => $widget_width,
                        'height' => $widget_height
                    ];
                    $current_dashboard_state['active_widgets'][] = $new_widget_entry;
                    saveDashboardState($current_dashboard_state); // Save, but don't fail the response if this save fails, as the widget file is created.

                    $response = ['status' => 'success', 'message' => "Widget '{$widget_name}' created successfully!"];
                } else {
                    unlink($widget_file_path); // Clean up created file if config save fails
                    $response['message'] = "Widget file created, but failed to update dynamic widgets configuration.";
                }
            } else {
                $response['message'] = "Failed to create widget file. Check server permissions for the 'widgets/' directory.";
            }
            break;
        case 'update_widget_details':
            $widget_id = $_POST['widget_id'] ?? '';
            $widget_name = trim($_POST['name'] ?? '');
            $widget_icon = trim($_POST['icon'] ?? '');
            $widget_width = (float)($_POST['width'] ?? 1.0);
            $widget_height = (float)($_POST['height'] ?? 1.0);

            if (empty($widget_id)) {
                $response['message'] = 'Widget ID is required for update.';
                break;
            }

            $current_dashboard_state = loadDashboardState();
            $widget_updated_in_active_list = false;

            // Update in active widgets list if present
            foreach ($current_dashboard_state['active_widgets'] as $key => $widget_entry) {
                if ($widget_entry['id'] === $widget_id) {
                    $current_dashboard_state['active_widgets'][$key]['width'] = $widget_width;
                    $current_dashboard_state['active_widgets'][$key]['height'] = $widget_height;
                    $widget_updated_in_active_list = true;
                    break;
                }
            }

            // Update in available_widgets (config.php) or dynamic_widgets.json
            global $available_widgets;
            if (isset($available_widgets[$widget_id])) {
                // Check if it's a dynamic widget
                $dynamic_widgets = loadDynamicWidgets();
                if (isset($dynamic_widgets[$widget_id])) {
                    // Update dynamic widget
                    $dynamic_widgets[$widget_id]['name'] = $widget_name;
                    $dynamic_widgets[$widget_id]['icon'] = $widget_icon;
                    $dynamic_widgets[$widget_id]['width'] = $widget_width;
                    $dynamic_widgets[$widget_id]['height'] = $widget_height;
                    saveDynamicWidgets($dynamic_widgets); // Save dynamic widgets
                } else {
                    // This is a static widget from config.php, we cannot directly modify config.php via this UI.
                    // For static widgets, only width/height in active_widgets will be affected.
                    // Name/icon changes won't persist unless config.php is manually edited.
                    // We'll still save the dashboard state for dimension changes.
                }

                // If dimensions were updated in active_widgets, save the dashboard state
                if ($widget_updated_in_active_list) {
                    saveDashboardState($current_dashboard_state);
                }
                
                $response = ['status' => 'success', 'message' => "Widget '{$widget_name}' details updated."];

            } else {
                $response['message'] = 'Widget ID not found in available widgets.';
            }
            break;
        case 'display_widget_settings_modal': // NEW: AJAX action to display the widget management table
            $output = '<p>Drag and drop widgets on the dashboard to reorder them visually.</p>';
            $output .= '<p>Widgets marked with <i class="fas fa-magic"></i> are dynamically created.</p>';
            $output .= '<h4>Active Widgets</h4>';
            $output .= '<table class="widget-settings-table">';
            $output .= '<thead><tr><th>ID</th><th>Name</th><th>Icon</th><th>W</th><th>H</th><th>Actions</th></tr></thead>';
            $output .= '<tbody>';

            $current_dashboard_state = loadDashboardState();
            foreach ($current_dashboard_state['active_widgets'] as $active_widget) {
                $widget_id = htmlspecialchars($active_widget['id']);
                
                // Get widget details (name, icon, default width/height) from available_widgets
                // This includes both static (from config.php) and dynamic widgets
                $widget_info = $available_widgets[$widget_id] ?? [
                    'name' => 'Unknown Widget',
                    'icon' => 'fas fa-question-circle',
                    'width' => 1,
                    'height' => 1
                ];

                $is_dynamic = isset($widget_info['dynamic']) && $widget_info['dynamic'];
                $dynamic_badge = $is_dynamic ? '<i class="fas fa-magic" title="Dynamically Created Widget"></i> ' : '';

                $output .= '<tr data-widget-id="' . $widget_id . '">';
                $output .= '<td>' . $dynamic_badge . $widget_id . '</td>';
                $output .= '<td><input type="text" class="form-control widget-setting-name" value="' . htmlspecialchars($widget_info['name']) . '" data-widget-id="' . $widget_id . '"></td>';
                $output .= '<td><input type="text" class="form-control widget-setting-icon" value="' . htmlspecialchars($widget_info['icon']) . '" data-widget-id="' . $widget_id . '"></td>';
                $output .= '<td><input type="number" class="form-control widget-setting-width" value="' . htmlspecialchars($active_widget['width']) . '" min="0.5" max="3" step="0.5" data-widget-id="' . $widget_id . '"></td>';
                $output .= '<td><input type="number" class="form-control widget-setting-height" value="' . htmlspecialchars($active_widget['height']) . '" min="0.5" max="4" step="0.5" data-widget-id="' . $widget_id . '"></td>';
                $output .= '<td><button class="btn btn-danger btn-sm remove-widget-btn" data-widget-id="' . $widget_id . '"><i class="fas fa-times"></i> Remove</button>';
                $output .= '<button class="btn btn-primary btn-sm update-widget-details-btn" data-widget-id="' . $widget_id . '" style="margin-left: 5px;"><i class="fas fa-save"></i> Save</button></td>';
                $output .= '</tr>';
            }
            $output .= '</tbody>';
            $output .= '</table>';

            $output .= '<h4>Available Widgets (Inactive)</h4>';
            $output .= '<div class="available-widgets-list">';
            $found_inactive = false;
            foreach ($available_widgets as $widget_id => $widget_info) {
                $is_active = false;
                foreach ($current_dashboard_state['active_widgets'] as $active_widget) {
                    if ($active_widget['id'] === $widget_id) {
                        $is_active = true;
                        break;
                    }
                }
                if (!$is_active) {
                    $dynamic_badge = isset($widget_info['dynamic']) && $widget_info['dynamic'] ? '<i class="fas fa-magic" title="Dynamically Created Widget"></i> ' : '';
                    $output .= '<button class="btn btn-sm add-widget-btn" data-widget-id="' . htmlspecialchars($widget_id) . '">';
                    $output .= '<i class="' . htmlspecialchars($widget_info['icon']) . '"></i> ' . $dynamic_badge . htmlspecialchars($widget_info['name']);
                    $output .= '</button>';
                    $found_inactive = true;
                }
            }
            if (!$found_inactive) {
                $output .= '<p>All available widgets are currently active.</p>';
            }
            $output .= '</div>';
            
            $response = ['status' => 'success', 'message' => 'Widget settings loaded.', 'html' => $output];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit(); // Terminate script after AJAX response
}

// This block will only execute if it's not an AJAX request.
// It handles widget adds/removes, global settings updates, and widget dimension updates (which still trigger full reloads).

// Load current dashboard state (settings + active widgets)
$current_dashboard_state = loadDashboardState();
$settings = $current_dashboard_state; // $settings now includes 'active_widgets' with dimensions

// IMPORTANT: Initialize $_SESSION['active_widgets'] from the loaded state
// This ensures session state is synced with persistent state on page load.
$_SESSION['active_widgets'] = $current_dashboard_state['active_widgets'];


// Handle form submissions for general settings and widget activation/deactivation (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_ajax_request) {
    if (isset($_POST['update_settings'])) {
        // Update general dashboard settings
        $settings['title'] = $_POST['title'] ?? $settings['title'];
        $settings['header_icon'] = $_POST['header_icon'] ?? $settings['header_icon']; // NEW: Save header icon
        $settings['accent_color'] = $_POST['accent_color'] ?? $settings['accent_color'];
        $settings['glass_intensity'] = (float)($_POST['glass_intensity'] ?? $settings['glass_intensity']);
        $settings['blur_amount'] = $_POST['blur_amount'] ?? $settings['blur_amount'];
        $settings['enable_animations'] = isset($_POST['enable_animations']);
        $settings['show_all_available_widgets'] = isset($_POST['show_all_available_widgets']);

        // Only save dimensions if they are explicitly sent from the form
        // (This part is mainly for the initial setup, gridstack handles most dimension changes via AJAX)
        if (isset($_POST['widget_dimensions']) && is_array($_POST['widget_dimensions'])) {
            foreach ($settings['active_widgets'] as &$active_widget) {
                $id = $active_widget['id'];
                if (isset($_POST['widget_dimensions'][$id]['width']) && isset($_POST['widget_dimensions'][$id]['height'])) {
                    $active_widget['width'] = max(0.5, min(3.0, (float)$_POST['widget_dimensions'][$id]['width']));
                    $active_widget['height'] = max(0.5, min(4.0, (float)$_POST['widget_dimensions'][$id]['height']));
                }
            }
            unset($active_widget); // Break the reference
        }
        
        saveDashboardState($settings);
        // After saving, redirect to GET to prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}


// Read Font Awesome icons for display in settings (optional: for icon picker UI)
// This is a placeholder. A real icon picker would involve more sophisticated JS.
$font_awesome_icons = [
    'fas fa-chart-line', 'fas fa-tasks', 'fas fa-calendar', 'fas fa-sticky-note',
    'fas fa-history', 'fas fa-print', 'fas fa-users', 'fas fa-bug', 'fas fa-code',
    'fas fa-gem', 'fas fa-star', 'fas fa-bell', 'fas fa-cog', 'fas fa-sync-alt',
    'fas fa-plus', 'fas fa-times', 'fas fa-save', 'fas fa-download', 'fas fa-trash-alt',
    'fas fa-undo', 'fas fa-magic', 'fas fa-question-circle', 'fas fa-cube',
    'fas fa-chart-bar', 'fas fa-info-circle', 'fas fa-exclamation-triangle'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/1.2.0/gridstack.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Dynamic CSS variables for theming */
        :root {
            --accent: <?= htmlspecialchars($settings['accent_color']) ?>;
            --glass-bg: rgba(35, 40, 49, <?= htmlspecialchars($settings['glass_intensity']) ?>);
            --blur-amount: <?= htmlspecialchars($settings['blur_amount']) ?>;
        }
        /* Optional: Animations toggle */
        body.no-animations * {
            transition: none !important;
        }
    </style>
</head>
<body class="<?= !$settings['enable_animations'] ? 'no-animations' : '' ?>">
    <div class="dashboard">
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="<?= htmlspecialchars($settings['header_icon']) ?>"></i>
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

        <aside class="sidebar">
            <h2>Widget Library</h2>
            <div class="widget-list">
                <?php foreach ($available_widgets as $widget_id => $widget_info): ?>
                    <?php
                        $is_active = false;
                        foreach ($settings['active_widgets'] as $active_widget) {
                            if ($active_widget['id'] === $widget_id) {
                                $is_active = true;
                                break;
                            }
                        }
                        $dynamic_badge = isset($widget_info['dynamic']) && $widget_info['dynamic'] ? '<i class="fas fa-magic" title="Dynamically Created Widget"></i> ' : '';
                    ?>
                    <div class="widget-item grid-stack-item-content <?= $is_active ? 'active' : '' ?>"
                         data-widget-id="<?= htmlspecialchars($widget_id) ?>"
                         data-gs-w="<?= htmlspecialchars($widget_info['width']) ?>"
                         data-gs-h="<?= htmlspecialchars($widget_info['height']) ?>"
                         data-gs-no-resize="true" data-gs-no-move="true">
                        <i class="<?= htmlspecialchars($widget_info['icon']) ?>"></i>
                        <?= $dynamic_badge ?><?= htmlspecialchars($widget_info['name']) ?>
                        <?php if ($is_active): ?>
                            <span>(Active)</span>
                        <?php else: ?>
                            <button class="add-widget-btn" data-widget-id="<?= htmlspecialchars($widget_id) ?>"><i class="fas fa-plus"></i> Add</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="main-content">
            <div class="grid-stack">
                <?php
                // Display active widgets based on loaded settings
                foreach ($settings['active_widgets'] as $widget_data) {
                    $widget_id = $widget_data['id'];
                    $position = $widget_data['position'];
                    $width = $widget_data['width'] ?? ($available_widgets[$widget_id]['width'] ?? 1);
                    $height = $widget_data['height'] ?? ($available_widgets[$widget_id]['height'] ?? 1);

                    // If show_all_available_widgets is true, and this widget is not in the default_dashboard_state
                    // or if it's a dynamic widget, ensure it gets a position if it somehow lost it.
                    // This is more for robustness if a position is missing.
                    if (!isset($widget_data['position'])) {
                        $widget_data['position'] = $position; // Re-assign if missing
                    }

                    echo '<div class="grid-stack-item" ' .
                         'data-gs-id="' . htmlspecialchars($widget_id) . '" ' .
                         'data-gs-x="' . htmlspecialchars(($position - 1) % 3) . '" ' . // Simple layout for initial load
                         'data-gs-y="' . htmlspecialchars(floor(($position - 1) / 3)) . '" ' .
                         'data-gs-w="' . htmlspecialchars($width) . '" ' .
                         'data-gs-h="' . htmlspecialchars($height) . '" ' .
                         'data-gs-no-resize="false" data-gs-no-move="false">';
                    echo '    <div class="grid-stack-item-content widget" data-widget-id="' . htmlspecialchars($widget_id) . '">';
                    echo '        <div class="widget-header">';
                    // Use widget_info from $available_widgets for dynamic name/icon, fall back to default if not found
                    $display_name = $available_widgets[$widget_id]['name'] ?? ucfirst(str_replace('_', ' ', $widget_id));
                    $display_icon = $available_widgets[$widget_id]['icon'] ?? 'fas fa-cube';
                    echo '            <h4 class="widget-title"><i class="' . htmlspecialchars($display_icon) . '"></i> ' . htmlspecialchars($display_name) . '</h4>';
                    echo '            <div class="widget-actions">';
                    echo '                <button class="remove-widget-btn" data-widget-id="' . htmlspecialchars($widget_id) . '" title="Remove from Dashboard"><i class="fas fa-times"></i></button>';
                    echo '            </div>';
                    echo '        </div>';
                    echo render_widget($widget_id); // Function from helpers.php to include widget content
                    echo '    </div>';
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>

    <div id="settings-overlay" class="modal-overlay"></div>
    <div id="settings-panel" class="settings-panel">
        <div class="settings-header">
            <h2><i class="fas fa-cog"></i> Dashboard Settings</h2>
            <button class="close-btn" id="close-settings">&times;</button>
        </div>
        <form id="dashboard-settings-form" method="POST" action="">
            <input type="hidden" name="update_settings" value="1">

            <div class="settings-group">
                <h3 class="settings-title">General Settings</h3>
                <div class="form-group">
                    <label for="dashboard-title">Dashboard Title</label>
                    <input type="text" id="dashboard-title" name="title" class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
                </div>
                <div class="form-group">
                    <label for="header-icon">Header Icon (Font Awesome 6 class)</label>
                    <input type="text" id="header-icon" name="header_icon" class="form-control" value="<?= htmlspecialchars($settings['header_icon']) ?>" placeholder="e.g., fas fa-gem, fas fa-star">
                    <small>Browse icons: <a href="https://fontawesome.com/v6/search" target="_blank" class="text-link">Font Awesome 6</a></small>
                </div>
                <div class="form-group">
                    <label>Enable Animations</label>
                    <label class="switch">
                        <input type="checkbox" id="enable-animations" name="enable_animations" <?= $settings['enable_animations'] ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="form-group">
                    <label>Show All Available Widgets in Library</label>
                    <label class="switch">
                        <input type="checkbox" id="show-all-available-widgets" name="show_all_available_widgets" <?= $settings['show_all_available_widgets'] ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="form-group">
                    <label>Theme Customization</label>
                    <button type="button" id="reset-theme-btn" class="btn" style="width: 100%;">
                        <i class="fas fa-undo"></i> Reset Theme to Default
                    </button>
                </div>
            </div>

            <div class="settings-group">
                <h3 class="settings-title">Appearance</h3>
                <div class="form-group">
                    <label for="accent-color">Accent Color</label>
                    <input type="color" id="accent-color" name="accent_color" class="form-control" value="<?= htmlspecialchars($settings['accent_color']) ?>">
                </div>
                <div class="form-group">
                    <label for="glass-intensity">Glass Effect Intensity</label>
                    <input type="range" id="glass-intensity" name="glass_intensity" class="form-control" min="0.1" max="1.0" step="0.05" value="<?= htmlspecialchars($settings['glass_intensity']) ?>">
                    <span><?= htmlspecialchars($settings['glass_intensity'] * 100) ?>%</span>
                </div>
                <div class="form-group">
                    <label for="blur-amount">Blur Amount</label>
                    <select id="blur-amount" name="blur_amount" class="form-control">
                        <option value="0px" <?= $settings['blur_amount'] == '0px' ? 'selected' : '' ?>>None (0px)</option>
                        <option value="5px" <?= $settings['blur_amount'] == '5px' ? 'selected' : '' ?>>Light (5px)</option>
                        <option value="10px" <?= $settings['blur_amount'] == '10px' ? 'selected' : '' ?>>Medium (10px)</option>
                        <option value="15px" <?= $settings['blur_amount'] == '15px' ? 'selected' : '' ?>>Strong (15px)</option>
                        <option value="20px" <?= $settings['blur_amount'] == '20px' ? 'selected' : '' ?>>Extra Strong (20px)</option>
                    </select>
                </div>
            </div>

            <div class="settings-group">
                <h3 class="settings-title">Widget Management</h3>
                <button type="button" id="manage-widgets-btn" class="btn" style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-cogs"></i> Manage Active Widgets
                </button>
                <button type="button" class="btn btn-secondary" id="open-create-widget-modal" style="width: 100%;">
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

    <div id="message-modal-overlay" class="modal-overlay">
        <div id="message-modal" class="modal-content">
            <div class="modal-header">
                <h2 id="message-modal-title"></h2>
                <button class="close-btn" id="close-message-modal">&times;</button>
            </div>
            <div class="modal-body" id="message-modal-content">
                </div>
            <div class="modal-footer" id="message-modal-footer">
                <button class="btn btn-secondary" id="cancel-message-modal">Cancel</button>
                <button class="btn btn-primary" id="confirm-message-modal">Confirm</button>
            </div>
        </div>
    </div>

    <div id="create-widget-modal-overlay" class="modal-overlay">
        <div id="create-widget-modal" class="modal-content">
            <div class="modal-header">
                <h2 id="create-widget-modal-title">Create New Widget Template</h2>
                <button class="close-btn" id="close-create-widget-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="create-widget-form">
                    <div class="form-group">
                        <label for="new-widget-id">Widget ID (Unique, lowercase_snake_case)</label>
                        <input type="text" id="new-widget-id" name="id" class="form-control" required
                               pattern="^[a-z0-9_]+$" title="Widget ID can only contain lowercase letters, numbers, and underscores."
                               placeholder="e.g., my_custom_widget">
                    </div>
                    <div class="form-group">
                        <label for="new-widget-name">Widget Name (Display Title)</label>
                        <input type="text" id="new-widget-name" name="name" class="form-control" required
                               placeholder="e.g., My Custom Report">
                    </div>
                    <div class="form-group">
                        <label for="new-widget-icon">Widget Icon (Font Awesome 6 class)</label>
                        <input type="text" id="new-widget-icon" name="icon" class="form-control" value="fas fa-cube"
                               placeholder="e.g., fas fa-chart-bar, fas fa-bell">
                        <small>Browse icons: <a href="https://fontawesome.com/v6/search" target="_blank" class="text-link">Font Awesome 6</a></small>
                    </div>
                    <div class="form-group half-width">
                        <label for="new-widget-width">Default Width (1-3)</label>
                        <input type="number" id="new-widget-width" name="width" class="form-control" value="1" min="1" max="3" step="1" required>
                    </div>
                    <div class="form-group half-width">
                        <label for="new-widget-height">Default Height (1-4)</label>
                        <input type="number" id="new-widget-height" name="height" class="form-control" value="1" min="1" max="4" step="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                        <i class="fas fa-plus"></i> Create Widget Template
                    </button>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/1.2.0/gridstack.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>