<?php
// Auto-generated endpoint: /DealerSupplyPriceListing/List [GET]
// Operation ID: DealerSupplyPriceListing/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupplyPriceListing/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
