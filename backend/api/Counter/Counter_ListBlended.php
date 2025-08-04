<?php
// Auto-generated endpoint: /Counter/ListBlended [POST]
// Operation ID: Counter/ListBlended

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Counter/ListBlended');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
