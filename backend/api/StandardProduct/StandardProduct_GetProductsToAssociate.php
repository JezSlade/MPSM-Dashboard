<?php
// Auto-generated endpoint: /StandardProduct/GetProductsToAssociate [GET]
// Operation ID: StandardProduct/GetProductsToAssociate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/StandardProduct/GetProductsToAssociate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
