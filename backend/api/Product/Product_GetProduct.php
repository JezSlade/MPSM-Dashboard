<?php
// Auto-generated endpoint: /Product/GetProduct [POST]
// Operation ID: Product/GetProduct

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Product/GetProduct');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
