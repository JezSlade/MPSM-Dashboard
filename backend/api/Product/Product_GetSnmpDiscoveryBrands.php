<?php
// Auto-generated endpoint: /Product/GetSnmpDiscoveryBrands [GET]
// Operation ID: Product/GetSnmpDiscoveryBrands

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Product/GetSnmpDiscoveryBrands');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
