<?php
// Auto-generated endpoint: /Dealer/CounterBlendToStandard/Create [POST]
// Operation ID: Dealer/CounterBlendToStandard/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Dealer/CounterBlendToStandard/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
