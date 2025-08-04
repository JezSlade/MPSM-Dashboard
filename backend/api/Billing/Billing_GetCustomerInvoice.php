<?php
// Auto-generated endpoint: /Billing/GetCustomerInvoice [POST]
// Operation ID: Billing/GetCustomerInvoice

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Billing/GetCustomerInvoice');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
