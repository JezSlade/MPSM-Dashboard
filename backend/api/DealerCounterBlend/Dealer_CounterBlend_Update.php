<?php
// Auto-generated endpoint: /Dealer/CounterBlend/Update [PUT]
// Operation ID: Dealer/CounterBlend/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/CounterBlend/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
