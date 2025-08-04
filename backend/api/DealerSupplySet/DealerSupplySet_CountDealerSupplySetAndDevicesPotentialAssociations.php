<?php
// Auto-generated endpoint: /DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations [GET]
// Operation ID: DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
