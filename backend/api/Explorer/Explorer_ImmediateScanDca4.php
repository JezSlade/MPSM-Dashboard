<?php
// Auto-generated endpoint: /Explorer/ImmediateScanDca4 [POST]
// Operation ID: Explorer/ImmediateScanDca4

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/ImmediateScanDca4');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
