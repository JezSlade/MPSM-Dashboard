<?php
// Auto-generated endpoint: /ShippedSupply/MassiveUpdate [POST]
// Operation ID: ShippedSupply/MassiveUpdate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ShippedSupply/MassiveUpdate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
