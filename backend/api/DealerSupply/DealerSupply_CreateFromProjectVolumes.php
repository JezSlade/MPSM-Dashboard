<?php
// Auto-generated endpoint: /DealerSupply/CreateFromProjectVolumes [POST]
// Operation ID: DealerSupply/CreateFromProjectVolumes

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/DealerSupply/CreateFromProjectVolumes');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
