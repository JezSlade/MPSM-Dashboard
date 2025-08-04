<?php
// Auto-generated endpoint: /CustomerNotification/GetNotificationPlaceholders [GET]
// Operation ID: CustomerNotification/GetNotificationPlaceholders

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/CustomerNotification/GetNotificationPlaceholders');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
