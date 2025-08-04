<?php
// Auto-generated endpoint: /Customer/UpdateCustomer [POST]
// Operation ID: Customer/UpdateCustomer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Customer/UpdateCustomer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
