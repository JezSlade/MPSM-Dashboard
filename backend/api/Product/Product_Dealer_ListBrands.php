<?php
// Auto-generated endpoint: /Product/Dealer/ListBrands [GET]
// Operation ID: Product/Dealer/ListBrands

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Product/Dealer/ListBrands');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
