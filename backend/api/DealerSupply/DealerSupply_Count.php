<?php
// Auto-generated endpoint: /DealerSupply/Count [GET]
// Operation ID: DealerSupply/Count

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/DealerSupply/Count');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
