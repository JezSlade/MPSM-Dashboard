<?php
// Auto-generated endpoint: /Billing/UpdateCustomerInvoice [PATCH]
// Operation ID: Billing/UpdateCustomerInvoice

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PATCH', '/Billing/UpdateCustomerInvoice');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
