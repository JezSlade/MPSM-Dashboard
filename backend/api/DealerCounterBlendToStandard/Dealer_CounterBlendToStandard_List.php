<?php
// Auto-generated endpoint: /Dealer/CounterBlendToStandard/List [GET]
// Operation ID: Dealer/CounterBlendToStandard/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Dealer/CounterBlendToStandard/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
