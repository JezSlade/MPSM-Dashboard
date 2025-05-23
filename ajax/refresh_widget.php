<?php
/**
 * AJAX handler for refreshing widgets
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

// Get widget ID from request
$widget_id = $_GET['widget_id'] ?? '';

if (empty($widget_id)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Widget ID is required'
    ]);
    exit;
}

// Get widget instance
$widget_registry = WidgetRegistry::getInstance();
$widget = $widget_registry->get_widget($widget_id);

if (!$widget) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Widget not found'
    ]);
    exit;
}

// Check if user has permission to view this widget
$user_id = Auth::getCurrentUserId();
$stmt = $db->prepare("SELECT 1 FROM user_widget_permissions WHERE user_id = ? AND widget_id = ? AND can_view = 1");
$stmt->execute([$user_id, $widget_id]);

if (!$stmt->fetchColumn()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Permission denied'
    ]);
    exit;
}

// Refresh widget data and render
$html = $widget->render();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'html' => $html
]);
