<?php
// Auto-generated endpoint: /Orders/GetOrderLineStatuses [GET]
// Operation ID: Orders/GetOrderLineStatuses

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Orders/GetOrderLineStatuses');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
