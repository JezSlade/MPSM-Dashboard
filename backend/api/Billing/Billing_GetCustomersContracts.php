<?php
// Auto-generated endpoint: /Billing/GetCustomersContracts [POST]
// Operation ID: Billing/GetCustomersContracts

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Billing/GetCustomersContracts');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
