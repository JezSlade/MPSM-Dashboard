<?php
// Auto-generated endpoint: /Product/GetModels [GET]
// Operation ID: Product/GetModels

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Product/GetModels');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
