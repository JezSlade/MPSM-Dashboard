<?php
// Auto-generated endpoint: /TraceVolume/ResetEvents [PUT]
// Operation ID: TraceVolume/ResetEvents

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/TraceVolume/ResetEvents');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
