<?php
// Auto-generated endpoint: /Dealer/CounterBlendToStandard/Update [PUT]
// Operation ID: Dealer/CounterBlendToStandard/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/CounterBlendToStandard/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
