<?php
// Auto-generated endpoint: /StandardProduct/ListOperations [GET]
// Operation ID: StandardProduct/ListOperations

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/StandardProduct/ListOperations');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
