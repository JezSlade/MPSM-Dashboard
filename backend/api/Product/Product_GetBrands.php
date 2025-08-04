<?php
// Auto-generated endpoint: /Product/GetBrands [GET]
// Operation ID: Product/GetBrands

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Product/GetBrands');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
