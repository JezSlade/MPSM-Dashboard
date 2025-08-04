<?php
// Auto-generated endpoint: /Billing/GetCustomerInvoicesList [POST]
// Operation ID: Billing/GetCustomerInvoicesList

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Billing/GetCustomerInvoicesList');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
