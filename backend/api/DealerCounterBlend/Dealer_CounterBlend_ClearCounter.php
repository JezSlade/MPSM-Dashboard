<?php
// Auto-generated endpoint: /Dealer/CounterBlend/ClearCounter [DELETE]
// Operation ID: Dealer/CounterBlend/ClearCounter

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Dealer/CounterBlend/ClearCounter');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
