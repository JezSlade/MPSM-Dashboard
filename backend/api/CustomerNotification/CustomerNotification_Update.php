<?php
// Auto-generated endpoint: /CustomerNotification/Update [PUT]
// Operation ID: CustomerNotification/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/CustomerNotification/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
