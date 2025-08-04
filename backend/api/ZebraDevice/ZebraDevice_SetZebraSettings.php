<?php
// Auto-generated endpoint: /ZebraDevice/SetZebraSettings [POST]
// Operation ID: ZebraDevice/SetZebraSettings

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ZebraDevice/SetZebraSettings');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
