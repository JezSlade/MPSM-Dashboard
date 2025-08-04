<?php
// Auto-generated endpoint: /Dealer/CounterBlend/Search [GET]
// Operation ID: Dealer/CounterBlend/Search

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Dealer/CounterBlend/Search');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
