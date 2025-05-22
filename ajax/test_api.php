<?php
/**
 * AJAX handler for testing API connection
 */
require_once '../core/config.php';
require_once '../core/auth.php';
require_once '../core/api.php';

// Require login and developer permissions
if (!Auth::isLoggedIn() || !Auth::isDeveloper()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Permission denied'
    ]);
    exit;
}

// Test API connection by getting an access token
$token = $api_client->getAccessToken();

if ($token) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'token' => true,
        'message' => 'API connection successful'
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to obtain access token'
    ]);
}
