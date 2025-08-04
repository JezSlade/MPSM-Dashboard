<?php
// Auto-generated endpoint: /Dealer/CounterBlend/Delete [DELETE]
// Operation ID: Dealer/CounterBlend/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Dealer/CounterBlend/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
