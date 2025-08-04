<?php
// Auto-generated endpoint: /Dealer/CounterBlend/Create [POST]
// Operation ID: Dealer/CounterBlend/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Dealer/CounterBlend/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
