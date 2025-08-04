<?php
// Auto-generated endpoint: /CustomerNotification/GetSampleNotification [GET]
// Operation ID: CustomerNotification/GetSampleNotification

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/CustomerNotification/GetSampleNotification');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
