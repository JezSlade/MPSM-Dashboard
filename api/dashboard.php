<?php
// api/dashboard.php

// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// Include configuration and classes
require_once __DIR__ . '/../config.php'; // Adjust path as needed
require_once __DIR__ . '/../src/php/DashboardManager.php';

session_start();

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request.'];

$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, DYNAMIC_WIDGETS_FILE, $available_widgets);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax_action = $_POST['ajax_action'] ?? '';

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

        case 'get_active_widgets_data':
            $current_dashboard_state = $dashboardManager->loadDashboardState();
            $active_widgets_data = [];
            foreach ($current_dashboard_state['active_widgets'] as $index => $widget_entry) {
                $widget_id = $widget_entry['id'];
                $widget_def = $available_widgets[$widget_id] ?? ['name' => 'Unknown Widget', 'icon' => 'question', 'width' => 1.0, 'height' => 1.0];
                $active_widgets_data[] = [
                    'id' => $widget_id,
                    'index' => $index,
                    'name' => $widget_def['name'],
                    'icon' => $widget_def['icon'],
                    'width' => (float)$widget_entry['width'],
                    'height' => (float)$widget_entry['height']
                ];
            }
            $response = ['status' => 'success', 'widgets' => $active_widgets_data];
            break;

        case 'update_single_widget_dimensions':
            $widget_index = (int)$_POST['widget_index'];
            $new_width = (float)$_POST['new_width'];
            $new_height = (float)$_POST['new_height'];

            $current_dashboard_state = $dashboardManager->loadDashboardState();
            $updated_active_widgets = $dashboardManager->updateWidgetDimensions($widget_index, $new_width, $new_height, $current_dashboard_state['active_widgets']);
            
            $current_dashboard_state['active_widgets'] = $updated_active_widgets;
            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; // Sync session
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

            $current_dashboard_state = $dashboardManager->loadDashboardState();
            $updated_active_widgets = $dashboardManager->updateWidgetOrder($new_order_ids, $current_dashboard_state['active_widgets']);
            
            $current_dashboard_state['active_widgets'] = $updated_active_widgets;
            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; // Sync session
                $response = ['status' => 'success', 'message' => 'Widget order updated.'];
            } else {
                $response['message'] = 'Failed to save widget order.';
            }
            break;

        case 'remove_widget_from_management':
            $widget_id_to_remove = $_POST['widget_id'];
            $current_dashboard_state = $dashboardManager->loadDashboardState();
            
            $updated_active_widgets = $dashboardManager->removeWidgetById($widget_id_to_remove, $current_dashboard_state['active_widgets']);

            // Check if any widget was actually removed by comparing counts
            $found = (count($current_dashboard_state['active_widgets']) > count($updated_active_widgets));

            if ($found) {
                $current_dashboard_state['active_widgets'] = $updated_active_widgets;
                if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                    $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; // Sync session
                    $response = ['status' => 'success', 'message' => 'Widget deactivated successfully.'];
                } else {
                    $response['message'] = 'Failed to save dashboard state after deactivation.';
                }
            } else {
                $response['message'] = 'Widget not found in active list.';
            }
            break;
        
        case 'add_widget': // Handle adding widget from sidebar drag-and-drop
            $widget_id_to_add = $_POST['widget_id'] ?? '';
            if (empty($widget_id_to_add)) {
                $response['message'] = 'Widget ID is required.';
                break;
            }
            $current_dashboard_state = $dashboardManager->loadDashboardState();
            // Only allow adding widgets if 'show all' is OFF
            if ($current_dashboard_state['show_all_available_widgets']) {
                $response['message'] = 'Cannot add widgets in "Show All Available Widgets" mode.';
                break;
            }

            $updated_active_widgets = $dashboardManager->addWidget($widget_id_to_add, $current_dashboard_state['active_widgets']);
            $current_dashboard_state['active_widgets'] = $updated_active_widgets;

            if ($dashboardManager->saveDashboardState($current_dashboard_state)) {
                $_SESSION['active_widgets'] = $current_dashboard_state['active_widgets']; // Sync session
                $response = ['status' => 'success', 'message' => 'Widget added successfully.'];
            } else {
                $response['message'] = 'Failed to add widget.';
            }
            break;

        case 'update_settings': // Handle global settings update
            $settings_from_post = [
                'title' => $_POST['dashboard_title'] ?? 'MPS Monitor Dashboard', // Updated default title
                'accent_color' => $_POST['accent_color'] ?? '#6366f1',
                'glass_intensity' => (float)($_POST['glass_intensity'] ?? 0.6),
                'blur_amount' => $_POST['blur_amount'] ?? '10px',
                'enable_animations' => isset($_POST['enable_animations']) && $_POST['enable_animations'] === '1',
                'show_all_available_widgets' => isset($_POST['show_all_available_widgets']) && $_POST['show_all_available_widgets'] === '1'
            ];
            
            $current_dashboard_state = $dashboardManager->loadDashboardState();
            $old_show_all_state = $current_dashboard_state['show_all_available_widgets'] ?? false;

            // Update current $settings array with new values from POST
            $updated_settings = array_merge($current_dashboard_state, $settings_from_post);
            
            // Special handling if 'show_all_available_widgets' was just turned ON
            if ($updated_settings['show_all_available_widgets'] && !$old_show_all_state) {
                $updated_settings['active_widgets'] = $dashboardManager->setAllAvailableWidgetsAsActive();
            }

            if ($dashboardManager->saveDashboardState($updated_settings)) {
                $_SESSION['dashboard_settings'] = $settings_from_post; // Update session for current request
                $_SESSION['active_widgets'] = $updated_settings['active_widgets']; // Sync active widgets in session
                $response = ['status' => 'success', 'message' => 'Settings updated successfully.'];
            } else {
                $response['message'] = 'Failed to save settings.';
            }
            break;

        case 'get_current_settings':
            // This action is for outputting current settings (for debug/export)
            $current_dashboard_state = $dashboardManager->loadDashboardState();
            $response = ['status' => 'success', 'settings' => $current_dashboard_state];
            break;

        case 'import_settings':
            $settings_data_json = $_POST['settings_data'] ?? '';
            $imported_settings = json_decode($settings_data_json, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($imported_settings)) {
                $response['message'] = 'Invalid JSON format for imported settings.';
                break;
            }

            // You might want to add more robust validation here to ensure the imported
            // settings structure is compatible with your dashboard's expectations.
            // For now, we'll trust the input given the user's security context.

            if ($dashboardManager->saveDashboardState($imported_settings)) {
                // After successful import, update session to reflect new state immediately
                $_SESSION['dashboard_settings'] = $imported_settings;
                $_SESSION['active_widgets'] = $imported_settings['active_widgets'] ?? [];
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
