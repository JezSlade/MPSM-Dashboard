<?php
// Auto-generated endpoint: /Orders/GetOrderProofOfDelivery [GET]
// Operation ID: Orders/GetOrderProofOfDelivery

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Orders/GetOrderProofOfDelivery');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
