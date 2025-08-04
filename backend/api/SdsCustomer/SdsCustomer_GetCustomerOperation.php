<?php
// Auto-generated endpoint: /SdsCustomer/GetCustomerOperation [GET]
// Operation ID: SdsCustomer/GetCustomerOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsCustomer/GetCustomerOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
