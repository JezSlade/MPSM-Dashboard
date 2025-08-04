<?php
// Auto-generated endpoint: /DealerSupplySet/List [GET]
// Operation ID: DealerSupplySet/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupplySet/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
