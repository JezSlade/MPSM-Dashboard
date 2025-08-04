<?php
// Auto-generated endpoint: /DealerSupplySet/CreateFromStandardModels [POST]
// Operation ID: DealerSupplySet/CreateFromStandardModels

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/DealerSupplySet/CreateFromStandardModels');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
