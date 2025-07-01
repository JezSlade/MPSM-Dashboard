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
require_once 'config.php';
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
    'header_icon' => 'fas fa-gem', // Default header icon
    'accent_color' => '#6366f1',
    'glass_intensity' => 0.6,
    'blur_amount' => '10px',
    'enable_animations' => true,
    'show_all_available_widgets' => false,
    'active_widgets' => [
        // Default widgets with initial x, y, width, height for GridStack
        ['id' => 'stats', 'x' => 0, 'y' => 0, 'width' => 2, 'height' => 1],
        ['id' => 'tasks', 'x' => 2, 'y' => 0, 'width' => 1, 'height' => 2],
        ['id' => 'calendar', 'x' => 3, 'y' => 0, 'width' => 1, 'height' => 1],
        ['id' => 'notes', 'x' => 0, 'y' => 1, 'width' => 1, 'height' => 1],
        ['id' => 'activity', 'x' => 1, 'y' => 1, 'width' => 2, 'height' => 1],
        ['id' => 'printers', 'x' => 3, 'y' => 1, 'width' => 1, 'height' => 1],
        ['id' => 'select_customer', 'x' => 0, 'y' => 2, 'width' => 1, 'height' => 1],
        ['id' => 'debug_info', 'x' => 1, 'y' => 2, 'width' => 2, 'height' => 2],
        ['id' => 'ide', 'x' => 4, 'y' => 0, 'width' => 3, 'height' => 3]
    ]
];

/**
 * Loads dashboard settings and active widgets from the JSON file.
 *
 * @return array Loaded settings including active_widgets or default state.
 */
function loadDashboardState() {
    global $default_dashboard_state, $available_widgets;

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

    // Ensure active_widgets entries have x, y, width, height, falling back to config defaults
    if (isset($final_state['active_widgets']) && is_array($final_state['active_widgets'])) {
        foreach ($final_state['active_widgets'] as $key => $widget_entry) {
            $widget_id = $widget_entry['id'];
            $default_width = (float)($available_widgets[$widget_id]['width'] ?? 1.0);
            $default_height = (float)($available_widgets[$widget_id]['height'] ?? 1.0);

            // Ensure x, y, width, height are set and clamped
            $final_state['active_widgets'][$key]['x'] = (int)($widget_entry['x'] ?? 0);
            $final_state['active_widgets'][$key]['y'] = (int)($widget_entry['y'] ?? 0);
            $final_state['active_widgets'][$key]['width'] = max(0.5, min(3.0, (float)($widget_entry['width'] ?? $default_width)));
            $final_state['active_widgets'][$key]['height'] = max(0.5, min(4.0, (float)($widget_entry['height'] ?? $default_height)));
        }
    } else {
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
    $result = file_put_contents(DASHBOARD_SETTINGS_FILE, $json_data);
    if ($result === false) {
        $error_message = "ERROR: saveDashboardState - Failed to write dashboard state to file: " . DASHBOARD_SETTINGS_FILE;
        if (!is_writable(dirname(DASHBOARD_SETTINGS_FILE))) {
             $error_message .= " - Directory not writable: " . dirname(DASHBOARD_SETTINGS_FILE);
        } else if (file_exists(DASHBOARD_SETTINGS_FILE) && !is_writable(DASHBOARD_SETTINGS_FILE)) {
             $error_message .= " - File exists but is not writable: " . DASHBOARD_SETTINGS_FILE;
        } else {
            $error_message .= " - Unknown write error.";
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
                $response = ['status' => 'success', 'message' => 'Settings file does not exist.'];
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
                    // Assign default x, y, and use default width/height from config.php
                    $new_widget_entry = [
                        'id' => $widget_id,
                        'x' => 0, // Default to 0,0 and let GridStack arrange
                        'y' => 0,
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
                // No need to re-index positions if using x,y for GridStack
                if (saveDashboardState($current_dashboard_state)) {
                    $response = ['status' => 'success', 'message' => 'Widget removed successfully.'];
                } else {
                    $response['message'] = 'Failed to remove widget (save error).';
                }
            } else {
                $response['message'] = 'Widget not found in active list.';
            }
            break;
        case 'update_widget_layout': // NEW: AJAX to update the layout (x, y, width, height) of widgets
            $layout_data = json_decode($_POST['layout'], true); // Expects an array of {id, x, y, width, height}
            if (!is_array($layout_data)) {
                $response['message'] = 'Invalid layout data received.';
                break;
            }

            $current_dashboard_state = loadDashboardState();
            $active_widgets_map = [];
            foreach ($current_dashboard_state['active_widgets'] as $widget) {
                $active_widgets_map[$widget['id']] = $widget;
            }

            $updated_active_widgets = [];
            foreach ($layout_data as $item) {
                $id = $item['id'];
                if (isset($active_widgets_map[$id])) {
                    $widget_entry = $active_widgets_map[$id];
                    $widget_entry['x'] = (int)$item['x'];
                    $widget_entry['y'] = (int)$item['y'];
                    $widget_entry['width'] = (float)$item['width'];
                    $widget_entry['height'] = (float)$item['height'];
                    $updated_active_widgets[] = $widget_entry;
                    unset($active_widgets_map[$id]); // Remove from map to track remaining
                }
            }
            // Add back any widgets that were active but not in the layout_data (shouldn't happen with GridStack)
            foreach ($active_widgets_map as $remaining_widget) {
                $updated_active_widgets[] = $remaining_widget;
            }

            $current_dashboard_state['active_widgets'] = $updated_active_widgets;
            if (saveDashboardState($current_dashboard_state)) {
                $response = ['status' => 'success', 'message' => 'Widget layout updated.'];
            } else {
                $response['message'] = 'Failed to save widget layout.';
            }
            break;
        case 'display_widget_settings_modal': // NEW: AJAX action to display the widget management table
            global $available_widgets;
            $output = '<p>Drag and drop widgets on the dashboard to reorder them visually.</p>';
            $output .= '<p>Widgets marked with <i class="fas fa-magic"></i> are dynamically created.</p>';
            $output .= '<h4>Active Widgets</h4>';
            $output .= '<table class="widget-management-table">';
            $output .= '<thead><tr><th>ID</th><th>Name</th><th>Icon</th><th>W</th><th>H</th><th>Actions</th></tr></thead>';
            $output .= '<tbody id="widget-management-table-body">'; // ID for JS to target

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
                // Use widget_info's name and icon for the inputs, and active_widget's width/height for inputs
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

            $output .= '<div class="available-widgets-list" style="margin-top: 20px;"><h4>Available Widgets (Inactive)</h4>';
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
        case 'update_widget_details': // NEW: AJAX to update individual widget details (name, icon, dimensions)
            $widget_id = $_POST['widget_id'] ?? '';
            $widget_name = trim($_POST['name'] ?? '');
            $widget_icon = trim($_POST['icon'] ?? '');
            $widget_width = (float)($_POST['width'] ?? 1.0);
            $widget_height = (float)($_POST['height'] ?? 1.0);

            if (empty($widget_id)) {
                $response['message'] = 'Widget ID is required for update.';
                break;
            }

            // Clamp dimensions
            $widget_width = max(0.5, min(3.0, $widget_width));
            $widget_height = max(0.5, min(4.0, $widget_height));

            $current_dashboard_state = loadDashboardState();
            $widget_updated_in_active_list = false;

            // Update in active widgets list (dimensions)
            foreach ($current_dashboard_state['active_widgets'] as $key => $widget_entry) {
                if ($widget_entry['id'] === $widget_id) {
                    $current_dashboard_state['active_widgets'][$key]['width'] = $widget_width;
                    $current_dashboard_state['active_widgets'][$key]['height'] = $widget_height;
                    $widget_updated_in_active_list = true;
                    break;
                }
            }

            // Update in dynamic_widgets.json if it's a dynamic widget
            $dynamic_widgets = loadDynamicWidgets();
            if (isset($dynamic_widgets[$widget_id])) {
                $dynamic_widgets[$widget_id]['name'] = $widget_name;
                $dynamic_widgets[$widget_id]['icon'] = $widget_icon;
                $dynamic_widgets[$widget_id]['width'] = $widget_width;
                $dynamic_widgets[$widget_id]['height'] = $widget_height;
                saveDynamicWidgets($dynamic_widgets); // Save dynamic widgets
            } else {
                // For static widgets from config.php, we only update dimensions in active_widgets.
                // Name/icon changes for static widgets won't persist via this UI.
            }

            // If dimensions were updated in active_widgets, save the dashboard state
            if ($widget_updated_in_active_list) {
                saveDashboardState($current_dashboard_state);
            }
            
            $response = ['status' => 'success', 'message' => "Widget '{$widget_name}' details updated."];
            break;
        case 'update_widget_details_batch': // NEW: AJAX to update multiple widget details (name, icon, dimensions)
            $updates = json_decode($_POST['updates'], true);
            if (!is_array($updates)) {
                $response['message'] = 'Invalid batch update data received.';
                break;
            }

            $current_dashboard_state = loadDashboardState();
            $dynamic_widgets = loadDynamicWidgets();
            $changes_made = false;

            foreach ($updates as $item) {
                $widget_id = $item['id'];
                $newName = trim($item['name'] ?? '');
                $newIcon = trim($item['icon'] ?? '');
                $newWidth = (float)($item['width'] ?? 1.0);
                $newHeight = (float)($item['height'] ?? 1.0);

                // Clamp dimensions
                $newWidth = max(0.5, min(3.0, $newWidth));
                $newHeight = max(0.5, min(4.0, $newHeight));

                // Update in active widgets list (dimensions)
                foreach ($current_dashboard_state['active_widgets'] as $key => $widget_entry) {
                    if ($widget_entry['id'] === $widget_id) {
                        if ($current_dashboard_state['active_widgets'][$key]['width'] !== $newWidth ||
                            $current_dashboard_state['active_widgets'][$key]['height'] !== $newHeight) {
                            $current_dashboard_state['active_widgets'][$key]['width'] = $newWidth;
                            $current_dashboard_state['active_widgets'][$key]['height'] = $newHeight;
                            $changes_made = true;
                        }
                        break;
                    }
                }

                // Update in dynamic_widgets.json if it's a dynamic widget
                if (isset($dynamic_widgets[$widget_id])) {
                    if ($dynamic_widgets[$widget_id]['name'] !== $newName ||
                        $dynamic_widgets[$widget_id]['icon'] !== $newIcon ||
                        $dynamic_widgets[$widget_id]['width'] !== $newWidth ||
                        $dynamic_widgets[$widget_id]['height'] !== $newHeight) {
                        
                        $dynamic_widgets[$widget_id]['name'] = $newName;
                        $dynamic_widgets[$widget_id]['icon'] = $newIcon;
                        $dynamic_widgets[$widget_id]['width'] = $newWidth;
                        $dynamic_widgets[$widget_id]['height'] = $newHeight;
                        $changes_made = true;
                    }
                }
            }

            if ($changes_made) {
                saveDynamicWidgets($dynamic_widgets); // Save dynamic widgets changes
                saveDashboardState($current_dashboard_state); // Save active widgets dimensions changes
                $response = ['status' => 'success', 'message' => 'All widget details updated successfully.'];
            } else {
                $response = ['status' => 'success', 'message' => 'No changes detected for widgets.'];
            }
            break;
        case 'create_new_widget_template': // NEW: AJAX to create a new widget file
            $widget_name = trim($_POST['name'] ?? '');
            $widget_id = trim($_POST['id'] ?? '');
            $widget_icon = trim($_POST['icon'] ?? 'fas fa-cube'); // Default to fas fa-cube
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
            
            // Clamp dimensions
            $widget_width = max(0.5, min(3.0, $widget_width));
            $widget_height = max(0.5, min(4.0, $widget_height));

            // Create the widget PHP file content
            // Note: The $widget_icon here should be the full class (e.g., 'fas fa-cube')
            $widget_template_content = "<?php\n" .
                "// widgets/{$widget_id}.php\n" .
                "// This widget was dynamically created. Feel free to modify its content.\n" .
                "// Its default configuration is stored in dynamic_widgets.json.\n" .
                "?>\n" .
                "<div class=\"widget-content\">\n" .
                "    <h3><i class=\"<?= htmlspecialchars(\$widget_icon ?? '{$widget_icon}')?>\"></i> <?= htmlspecialchars(\$widget_name ?? '{$widget_name}') ?></h3>\n" .
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
                        'x' => 0, // Default x, y
                        'y' => 0,
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

// Handle POST requests for widget management and settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // This will only be true for non-AJAX POSTs now
    $has_state_changed = false; // Flag to know if we need to save the state

    // Check the 'action_type' to dispatch
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'update_settings') {
        // Update general dashboard settings
        $settings_from_post = [
            'title' => $_POST['title'] ?? 'Glass Dashboard', // Corrected name from dashboard_title
            'header_icon' => $_POST['header_icon'] ?? 'fas fa-gem', // NEW: Save header icon
            'accent_color' => $_POST['accent_color'] ?? '#6366f1',
            'glass_intensity' => (float)($_POST['glass_intensity'] ?? 0.6),
            'blur_amount' => $_POST['blur_amount'] ?? '10px',
            'enable_animations' => isset($_POST['enable_animations']) && $_POST['enable_animations'] === '1',
            'show_all_available_widgets' => isset($_POST['show_all_available_widgets']) && $_POST['show_all_available_widgets'] === '1'
        ];
        
        // Update current $settings array with new values from POST
        $settings = array_merge($settings, $settings_from_post);
        $has_state_changed = true;

        // Special handling if 'show_all_available_widgets' was just turned ON
        if ($settings['show_all_available_widgets'] && !($current_dashboard_state['show_all_available_widgets'] ?? false)) {
            // Overwrite active_widgets with all available widgets, sorted alphabetically by ID,
            // using their default dimensions from config.php
            $new_active_widgets = [];
            foreach ($available_widgets as $id => $def) {
                $new_active_widgets[] = [
                    'id' => $id,
                    'x' => 0, // Default x, y for new widgets when 'show all' is enabled
                    'y' => 0,
                    'width' => (float)($def['width'] ?? 1.0),
                    'height' => (float)($def['height'] ?? 1.0)
                ];
            }
            usort($new_active_widgets, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });
            $settings['active_widgets'] = $new_active_widgets; // Update settings array directly
        }

    }
    // No other direct POST actions are expected here, as they are now handled by AJAX.

    // If any state (settings or active widgets) changed, save the entire state
    if ($has_state_changed) {
        if (!saveDashboardState($settings)) { // Save the entire $settings array
            error_log("CRITICAL ERROR: index.php - Failed to save dashboard state persistently. Check server error logs for more details!");
        }
    }

    // Always redirect to GET after a POST to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ensure the $settings array used for rendering always reflects the latest state,
// potentially updated by POST or loaded from persistence.
// This merge ensures default values are applied, then persistent ones.
$settings = loadDashboardState(); // Reload the state to ensure it's fresh after any POST operations

// Pass available widgets to the view
global $available_widgets;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/1.2.0/gridstack.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        :root {
            --accent: <?= htmlspecialchars($settings['accent_color']) ?>;
            --glass-bg: rgba(35, 40, 49, <?= htmlspecialchars($settings['glass_intensity']) ?>);
            --blur-amount: <?= htmlspecialchars($settings['blur_amount']) ?>;
        }
        /* Apply transition conditionally based on enable_animations setting */
        <?php if ($settings['enable_animations']): ?>
        .widget {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .widget:hover {
            transform: translateY(-5px);
            box-shadow:
                12px 12px 24px var(--shadow-dark),
                -12px -12px 24px rgba(74, 78, 94, 0.1);
        }
        <?php else: ?>
        .widget, .widget *, .settings-panel, .message-modal-overlay, .message-modal, .widget-expanded-overlay {
            transition: none !important;
            animation: none !important;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <!-- New: Overlay for expanded widgets -->
    <div class="widget-expanded-overlay" id="widget-expanded-overlay"></div>

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
                <button class="btn btn-secondary" id="cancel-message-modal">Cancel</button>
                <button class="btn btn-primary" id="confirm-message-modal">OK</button>
            </div>
        </div>
    </div>

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
                        <label for="new-widget-icon">Font Awesome Icon Class (e.g., fas fa-chart-bar)</label>
                        <input type="text" id="new-widget-icon" name="icon" class="form-control" value="fas fa-cube" placeholder="e.g., fas fa-chart-bar">
                    </div>
                    <div class="form-group">
                        <label for="new-widget-width">Default Width (1-3 grid units)</label>
                        <input type="number" id="new-widget-width" name="width" class="form-control" value="1" min="1" max="3" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="new-widget-height">Default Height (1-4 grid units)</label>
                        <input type="number" id="new-widget-height" name="height" class="form-control" value="1" min="1" max="4" step="1" required>
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
            </div>

            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php foreach ($available_widgets as $id => $widget_info): ?>
                    <div class="widget-item" draggable="true"
                         data-widget-id="<?= htmlspecialchars($id) ?>"
                         data-gs-w="<?= htmlspecialchars($widget_info['width']) ?>"
                         data-gs-h="<?= htmlspecialchars($widget_info['height']) ?>">
                        <i class="<?= htmlspecialchars($widget_info['icon']) ?>"></i>
                        <span class="widget-name"><?= htmlspecialchars($widget_info['name']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="manage-widgets-btn">
                    <i class="fas fa-th-large"></i>
                    <span>Manage Widgets</span>
                </div>
                <div class="nav-item" id="open-create-widget-modal-sidebar">
                    <i class="fas fa-file-code"></i>
                    <span>Create New Widget</span>
                </div>
                <div class="nav-item" id="theme-settings-btn-sidebar">
                    <i class="fas fa-palette"></i>
                    <span>Theme Settings</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="grid-stack">
                <?php
                // Determine which widgets to display based on 'show_all_available_widgets' setting
                $widgets_to_render = [];
                if ($settings['show_all_available_widgets']) {
                    // If 'show all' is true, render ALL available widgets from config.php
                    foreach ($available_widgets as $id => $def) {
                        $widgets_to_render[] = [
                            'id' => $id,
                            'x' => 0, 'y' => 0, // GridStack will arrange them
                            'width' => (float)($def['width'] ?? 1.0),
                            'height' => (float)($def['height'] ?? 1.0)
                        ];
                    }
                    // Sort them alphabetically by ID for consistent initial placement
                    usort($widgets_to_render, function($a, $b) {
                        return strcmp($a['id'], $b['id']);
                    });
                } else {
                    // Otherwise, render the active widgets from persistent storage
                    $widgets_to_render = $settings['active_widgets'];
                }

                foreach ($widgets_to_render as $widget_data):
                    $widget_id = $widget_data['id'];
                    // Use a fallback for widget_def if config.php isn't correctly loading it
                    $widget_def = $available_widgets[$widget_id] ?? ['name' => 'Unknown Widget', 'icon' => 'fas fa-question-circle', 'width' => 1.0, 'height' => 1.0];
                    
                    // Use the dimensions from the active_widgets array if present, otherwise fall back to config default
                    $current_x = (int)($widget_data['x'] ?? 0);
                    $current_y = (int)($widget_data['y'] ?? 0);
                    $current_width = (float)($widget_data['width'] ?? $widget_def['width']);
                    $current_height = (float)($widget_data['height'] ?? $widget_def['height']);
                ?>
                <div class="grid-stack-item"
                     data-gs-id="<?= htmlspecialchars($widget_id) ?>"
                     data-gs-x="<?= htmlspecialchars($current_x) ?>"
                     data-gs-y="<?= htmlspecialchars($current_y) ?>"
                     data-gs-w="<?= htmlspecialchars($current_width) ?>"
                     data-gs-h="<?= htmlspecialchars($current_height) ?>">
                    <div class="grid-stack-item-content widget" data-widget-id="<?= htmlspecialchars($widget_id) ?>">
                        <div class="widget-header">
                            <h4 class="widget-title">
                                <i class="<?= htmlspecialchars($widget_def['icon']) ?>"></i>
                                <span><?= htmlspecialchars($widget_def['name']) ?></span>
                            </h4>
                            <div class="widget-actions">
                                <?php if ($widget_id === 'ide'): // Only IDE widget has expand/collapse ?>
                                    <div class="widget-action action-expand" title="Expand/Collapse">
                                        <i class="fas fa-expand"></i>
                                    </div>
                                <?php endif; ?>
                                <button class="remove-widget-btn" data-widget-id="<?= htmlspecialchars($widget_id) ?>" title="Remove from Dashboard">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="widget-content">
                            <?= render_widget($widget_id) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/1.2.0/gridstack.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>
