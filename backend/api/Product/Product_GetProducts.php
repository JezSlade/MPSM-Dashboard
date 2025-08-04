<?php
// Auto-generated endpoint: /Product/GetProducts [POST]
// Operation ID: Product/GetProducts

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Product/GetProducts');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
