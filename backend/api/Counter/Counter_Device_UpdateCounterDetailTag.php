<?php
// Auto-generated endpoint: /Counter/Device/UpdateCounterDetailTag [POST]
// Operation ID: Counter/Device/UpdateCounterDetailTag

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Counter/Device/UpdateCounterDetailTag');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
