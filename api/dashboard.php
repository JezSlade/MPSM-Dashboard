<?php
// api/dashboard.php

// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// Ensure no whitespace or output before this line
// Start output buffering as early as possible
ob_start();

// Include configuration and classes
require_once __DIR__ . '/../config.php'; // Adjust path as needed
require_once __DIR__ . '/../src/php/DashboardManager.php';
require_once __DIR__ . '/../src/php/FileManager.php'; // Needed for widget creation

session_start();

// Clear any buffered output before setting header
// This ensures no accidental whitespace or errors precede the JSON
ob_clean();
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request.'];

// Instantiate DashboardManager with the dynamically discovered widgets
// $available_widgets is populated by discover_widgets() in config.php
$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, $available_widgets);
$fileManager = new FileManager(APP_ROOT); // Instantiate FileManager for widget creation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax_action = $_POST['ajax_action'] ?? '';

    // Load current dashboard state at the beginning of each POST request
    // This ensures we always work with the latest persistent data.
    $current_dashboard_state = $dashboardManager->loadDashboardState();
    
    switch ($ajax_action) {
        case 'delete_settings_json':
            if (file_exists(DASHBOARD_SETTINGS_FILE)) {
                if (unlink(DASHBOARD_SETTINGS_FILE)) {
                    session_destroy(); // Clear session to ensure default state is loaded on refresh
                    session_start(); // Start a new session
                    $response = ['status' => 'success', 'message' => 'Dashboard settings reset successfully.'];
                } else {
                    $response['message'] = "Failed to delete settings file. Check permissions.";
                }
            } else {
                $response['message'] = "Settings file does not exist.";
            }
            break;

        case 'get_all_widget_states': // New action to get all widget states (active/deactivated) for management table
            // This now returns the full 'widgets_state' array from the dashboard settings
            $response = ['status' => 'success', 'widgets_state' => array_values($current_dashboard_state['widgets_state'])];
            break;

        case 'update_single_widget_dimensions':
            $widget_id = $_POST['widget_id'] ?? ''; // Now using widget_id
            $new_width = (float)($_POST['new_width'] ?? 0);
            $new_height = (float)($_POST['new_height'] ?? 0);

            if (empty($widget_id)) {
                $response['message'] = 'Widget ID is missing.';
                break;
            }

            $updated_widgets_state = $dashboardManager->updateWidgetDimensions($widget_id, $new_width, $new_height, $current_dashboard_state['widgets_state']);
            
            $current_dashboard_state['widgets_state'] = $updated_widgets_state;
            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['dashboard_settings'] = $current_dashboard_state; // Sync session
                $response = ['status' => 'success', 'message' => 'Widget dimensions updated.'];
            } else {
                $response['message'] = 'Failed to save widget dimensions.';
            }
            break;

        case 'update_widget_order':
            $new_order_ids = json_decode($_POST['order'], true);
            if (!is_array($new_order_ids)) {
                $response['message'] = 'Invalid order data received.';
                break;
            }

            // The updateWidgetOrder now handles re-positioning all widgets, active or not
            $updated_widgets_state = $dashboardManager->updateWidgetOrder($new_order_ids, $current_dashboard_state['widgets_state']);
            
            $current_dashboard_state['widgets_state'] = $updated_widgets_state;
            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['dashboard_settings'] = $current_dashboard_state; // Sync session
                $response = ['status' => 'success', 'message' => 'Widget order updated.'];
            } else {
                $response['message'] = 'Failed to save widget order.';
            }
            break;

        case 'toggle_widget_active_status': // New action to toggle active status
            $widget_id = $_POST['widget_id'] ?? '';
            $is_active = filter_var($_POST['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (empty($widget_id)) {
                $response['message'] = 'Widget ID is missing.';
                break;
            }

            $updated_widgets_state = $dashboardManager->updateWidgetActiveStatus($widget_id, $is_active, $current_dashboard_state['widgets_state']);
            
            $current_dashboard_state['widgets_state'] = $updated_widgets_state;
            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['dashboard_settings'] = $current_dashboard_state; // Sync session
                $response = ['status' => 'success', 'message' => 'Widget status updated.'];
            } else {
                $response['message'] = 'Failed to update widget status.';
            }
            break;
        
        case 'create_new_widget_template': // This action now creates the file and relies on discovery
            $widget_id = $_POST['id'] ?? '';
            $widget_name = $_POST['name'] ?? '';
            $widget_icon = $_POST['icon'] ?? 'cube';
            $widget_width = (float)($_POST['width'] ?? 1.0);
            $widget_height = (float)($_POST['height'] ?? 1.0);

            if (empty($widget_id) || empty($widget_name)) {
                $response['message'] = 'Widget ID and Name are required.';
                break;
            }

            // Use FileManager to create the PHP template file
            if ($fileManager->createWidgetTemplateFile($widget_id, $widget_name, $widget_icon, $widget_width, $widget_height)) {
                // The widget will be automatically discovered on next loadDashboardState()
                $response = ['status' => 'success', 'message' => 'Widget template created successfully. Reloading to discover new widget...'];
            } else {
                $response['message'] = 'Failed to create widget template. It might already exist or permissions are incorrect.';
            }
            break;

        case 'update_settings': // Handle global settings update
            $settings_from_post = [
                'title' => $_POST['dashboard_title'] ?? 'MPS Monitor Dashboard',
                'site_icon' => $_POST['site_icon'] ?? 'gem',
                'accent_color' => $_POST['accent_color'] ?? '#6366f1',
                'glass_intensity' => (float)($_POST['glass_intensity'] ?? 0.6),
                'blur_amount' => $_POST['blur_amount'] ?? '10px',
                'enable_animations' => isset($_POST['enable_animations']) && $_POST['enable_animations'] === '1',
                'show_all_available_widgets' => isset($_POST['show_all_available_widgets']) && $_POST['show_all_available_widgets'] === '1'
            ];
            
            // Update current $settings array with new values from POST
            $updated_settings = array_merge($current_dashboard_state, $settings_from_post);
            
            if ($dashboardManager->saveDashboardState($updated_settings)) {
                $_SESSION['dashboard_settings'] = $updated_settings; // Update session for current request
                $response = ['status' => 'success', 'message' => 'Settings updated successfully.'];
            } else {
                $response['message'] = 'Failed to save settings.';
            }
            break;

        case 'get_current_settings':
            // This action is for outputting current settings (for debug/export)
            $response = ['status' => 'success', 'settings' => $current_dashboard_state];
            break;

        case 'import_settings':
            $settings_data_json = $_POST['settings_data'] ?? '';
            $imported_settings = json_decode($settings_data_json, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($imported_settings)) {
                $response['message'] = 'Invalid JSON format for imported settings.';
                break;
            }

            if ($dashboardManager->saveDashboardState($imported_settings)) {
                // After successful import, update session to reflect new state immediately
                $_SESSION['dashboard_settings'] = $imported_settings;
                $response = ['status' => 'success', 'message' => 'Settings imported successfully.'];
            } else {
                $response['message'] = 'Failed to save imported settings.';
            }
            break;

        default:
            $response['message'] = 'Unknown dashboard AJAX action.';
            break;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
?>
