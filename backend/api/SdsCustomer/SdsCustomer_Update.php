<?php
// Auto-generated endpoint: /SdsCustomer/Update [PATCH]
// Operation ID: SdsCustomer/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PATCH', '/SdsCustomer/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
