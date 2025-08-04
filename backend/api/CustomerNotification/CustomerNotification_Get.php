<?php
// Auto-generated endpoint: /CustomerNotification/Get [GET]
// Operation ID: CustomerNotification/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/CustomerNotification/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
