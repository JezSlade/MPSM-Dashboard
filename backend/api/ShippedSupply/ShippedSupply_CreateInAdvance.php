<?php
// Auto-generated endpoint: /ShippedSupply/CreateInAdvance [POST]
// Operation ID: ShippedSupply/CreateInAdvance

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ShippedSupply/CreateInAdvance');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
