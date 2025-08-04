<?php
// Auto-generated endpoint: /DealerSupplyPriceListing/Update [PUT]
// Operation ID: DealerSupplyPriceListing/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/DealerSupplyPriceListing/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
