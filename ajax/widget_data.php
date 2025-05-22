<?php
/**
 * AJAX handler for widget data operations
 */
require_once '../core/config.php';
require_once '../core/auth.php';
require_once '../core/widget_registry.php';

// Require login
if (!Auth::isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get action from request
$action = $_GET['action'] ?? '';

if (empty($action)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Action is required'
    ]);
    exit;
}

// Handle different actions
switch ($action) {
    case 'get_time':
        // Get current time in specified format
        $format = $_GET['format'] ?? 'F j, Y g:i A';
        $time = date($format);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'time' => $time,
            'timestamp' => time()
        ]);
        break;
        
    case 'get_widget_data':
        // Get data for a specific widget
        $widget_id = $_GET['widget_id'] ?? '';
        $data_key = $_GET['key'] ?? '';
        
        if (empty($widget_id) || empty($data_key)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Widget ID and data key are required'
            ]);
            exit;
        }
        
        // Get widget data from database
        $stmt = $db->prepare("SELECT data_value FROM widget_data WHERE widget_id = ? AND data_key = ?");
        $stmt->execute([$widget_id, $data_key]);
        $data_value = $stmt->fetchColumn();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'value' => $data_value
        ]);
        break;
        
    case 'set_widget_data':
        // Set data for a specific widget
        $widget_id = $_POST['widget_id'] ?? '';
        $data_key = $_POST['key'] ?? '';
        $data_value = $_POST['value'] ?? '';
        
        if (empty($widget_id) || empty($data_key)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Widget ID and data key are required'
            ]);
            exit;
        }
        
        // Check if user has permission to edit this widget
        $user_id = Auth::getCurrentUserId();
        $stmt = $db->prepare("SELECT 1 FROM user_widget_permissions WHERE user_id = ? AND widget_id = ? AND can_edit = 1");
        $stmt->execute([$user_id, $widget_id]);
        
        if (!$stmt->fetchColumn()) {
            header  $widget_id]);
        
        if (!$stmt->fetchColumn()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Permission denied'
            ]);
            exit;
        }
        
        // Set widget data in database
        $stmt = $db->prepare("
            INSERT INTO widget_data (widget_id, data_key, data_value) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE data_value = VALUES(data_value)
        ");
        
        $result = $stmt->execute([$widget_id, $data_key, $data_value]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Data saved successfully' : 'Failed to save data'
        ]);
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unknown action'
        ]);
        break;
}
