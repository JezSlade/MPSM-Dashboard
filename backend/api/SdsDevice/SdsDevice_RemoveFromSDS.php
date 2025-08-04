<?php
// Auto-generated endpoint: /SdsDevice/RemoveFromSDS [DELETE]
// Operation ID: SdsDevice/RemoveFromSDS

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/SdsDevice/RemoveFromSDS');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
