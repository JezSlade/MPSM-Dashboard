<?php
// Auto-generated endpoint: /Dealer/CounterBlend/List [GET]
// Operation ID: Dealer/CounterBlend/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Dealer/CounterBlend/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
