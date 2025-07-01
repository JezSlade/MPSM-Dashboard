<?php
// api/widget_creation.php

// PHP Debugging Lines - START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// Include configuration and classes
require_once __DIR__ . '/../config.php'; // Adjust path as needed
require_once __DIR__ . '/../src/php/DashboardManager.php';
require_once __DIR__ . '/../src/php/FileManager.php'; // Needed for createWidgetTemplate

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request.'];

$dashboardManager = new DashboardManager(DASHBOARD_SETTINGS_FILE, DYNAMIC_WIDGETS_FILE, $available_widgets);
$fileManager = new FileManager(APP_ROOT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax_action = $_POST['ajax_action'] ?? '';

    if ($ajax_action === 'create_new_widget_template') {
        $widget_name = trim($_POST['name'] ?? '');
        $widget_id = trim($_POST['id'] ?? '');
        $widget_icon = trim($_POST['icon'] ?? 'cube');
        $widget_width = (float)($_POST['width'] ?? 1.0);
        $widget_height = (float)($_POST['height'] ?? 1.0);

        // Basic validation
        if (empty($widget_name) || empty($widget_id)) {
            $response['message'] = 'Widget Name and ID are required.';
            echo json_encode($response);
            exit;
        }
        if (!preg_match('/^[a-z0-9_]+$/', $widget_id)) {
            $response['message'] = 'Widget ID can only contain lowercase letters, numbers, and underscores.';
            echo json_encode($response);
            exit;
        }

        if ($fileManager->createWidgetTemplate($widget_id, $widget_name, $widget_icon, $widget_width, $widget_height)) {
            // Update dynamic_widgets.json
            $dynamic_widgets = $dashboardManager->loadDynamicWidgets();
            $dynamic_widgets[$widget_id] = [
                'name' => $widget_name,
                'icon' => $widget_icon,
                'width' => $widget_width,
                'height' => $widget_height
            ];

            if ($dashboardManager->saveDynamicWidgets($dynamic_widgets)) {
                $response = ['status' => 'success', 'message' => 'New widget template created and registered successfully! Reloading...'];
            } else {
                // If saving dynamic_widgets.json fails, try to clean up the created PHP file
                unlink(APP_ROOT . '/widgets/' . $widget_id . '.php');
                $response['message'] = 'Failed to save widget configuration. Widget file created but not registered. Please try again.';
            }
        } else {
            $response['message'] = 'Failed to create widget file. It might already exist or permissions are incorrect.';
        }
    } else {
        $response['message'] = 'Unknown widget creation AJAX action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
?>
