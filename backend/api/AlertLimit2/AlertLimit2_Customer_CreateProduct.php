<?php
// Auto-generated endpoint: /AlertLimit2/Customer/CreateProduct [POST]
// Operation ID: AlertLimit2/Customer/CreateProduct

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/AlertLimit2/Customer/CreateProduct');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
