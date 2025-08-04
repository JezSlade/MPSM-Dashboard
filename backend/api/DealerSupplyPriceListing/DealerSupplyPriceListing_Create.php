<?php
// Auto-generated endpoint: /DealerSupplyPriceListing/Create [POST]
// Operation ID: DealerSupplyPriceListing/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/DealerSupplyPriceListing/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
