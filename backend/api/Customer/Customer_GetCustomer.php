<?php
// Auto-generated endpoint: /Customer/GetCustomer [POST]
// Operation ID: Customer/GetCustomer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Customer/GetCustomer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
