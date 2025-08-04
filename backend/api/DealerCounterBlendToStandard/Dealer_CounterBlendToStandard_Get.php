<?php
// Auto-generated endpoint: /Dealer/CounterBlendToStandard/Get [GET]
// Operation ID: Dealer/CounterBlendToStandard/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Dealer/CounterBlendToStandard/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
