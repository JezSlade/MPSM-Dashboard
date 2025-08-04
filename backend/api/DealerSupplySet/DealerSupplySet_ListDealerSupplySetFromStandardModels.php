<?php
// Auto-generated endpoint: /DealerSupplySet/ListDealerSupplySetFromStandardModels [GET]
// Operation ID: DealerSupplySet/ListDealerSupplySetFromStandardModels

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupplySet/ListDealerSupplySetFromStandardModels');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
