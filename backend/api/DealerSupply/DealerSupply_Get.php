<?php
// Auto-generated endpoint: /DealerSupply/Get [GET]
// Operation ID: DealerSupply/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupply/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
