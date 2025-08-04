<?php
// Auto-generated endpoint: /Orders/GetOrderAdditionalInfo [GET]
// Operation ID: Orders/GetOrderAdditionalInfo

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Orders/GetOrderAdditionalInfo');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
