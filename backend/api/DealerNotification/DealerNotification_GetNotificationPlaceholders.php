<?php
// Auto-generated endpoint: /DealerNotification/GetNotificationPlaceholders [GET]
// Operation ID: DealerNotification/GetNotificationPlaceholders

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerNotification/GetNotificationPlaceholders');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
