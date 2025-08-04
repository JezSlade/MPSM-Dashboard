<?php
// Auto-generated endpoint: /Customer/GetCustomerByCode [POST]
// Operation ID: Customer/GetCustomerByCode

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Customer/GetCustomerByCode');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
