<?php
// Auto-generated endpoint: /DealerSupplyPriceListing/Delete [DELETE]
// Operation ID: DealerSupplyPriceListing/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/DealerSupplyPriceListing/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
