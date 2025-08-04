<?php
// Auto-generated endpoint: /Billing/GetDealerInvoicesList [POST]
// Operation ID: Billing/GetDealerInvoicesList

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Billing/GetDealerInvoicesList');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
