<?php
// Auto-generated endpoint: /Orders/GetOrderLines [GET]
// Operation ID: Orders/GetOrderLines

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Orders/GetOrderLines');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
