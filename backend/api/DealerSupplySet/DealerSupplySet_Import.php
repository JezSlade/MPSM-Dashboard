<?php
// Auto-generated endpoint: /DealerSupplySet/Import [POST]
// Operation ID: DealerSupplySet/Import

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/DealerSupplySet/Import');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
