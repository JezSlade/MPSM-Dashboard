<?php
// Auto-generated endpoint: /AlertLimit/Customer/Product/List [GET]
// Operation ID: AlertLimit/Customer/Product/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/AlertLimit/Customer/Product/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
