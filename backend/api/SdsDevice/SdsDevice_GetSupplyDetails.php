<?php
// Auto-generated endpoint: /SdsDevice/GetSupplyDetails [GET]
// Operation ID: SdsDevice/GetSupplyDetails

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetSupplyDetails');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
