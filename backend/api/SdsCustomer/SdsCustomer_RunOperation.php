<?php
// Auto-generated endpoint: /SdsCustomer/RunOperation [PUT]
// Operation ID: SdsCustomer/RunOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/SdsCustomer/RunOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
