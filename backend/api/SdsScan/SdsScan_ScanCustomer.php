<?php
// Auto-generated endpoint: /SdsScan/ScanCustomer [POST]
// Operation ID: SdsScan/ScanCustomer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsScan/ScanCustomer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
