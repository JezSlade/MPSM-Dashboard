<?php
// Auto-generated endpoint: /DealerSupply/ListSuggested [POST]
// Operation ID: DealerSupply/ListSuggested

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/DealerSupply/ListSuggested');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
