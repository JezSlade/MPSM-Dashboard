<?php
// Auto-generated endpoint: /DealerSupplyPriceListing/UpdateByCustomer [PUT]
// Operation ID: DealerSupplyPriceListing/UpdateByCustomer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/DealerSupplyPriceListing/UpdateByCustomer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
