<?php
// Auto-generated endpoint: /StandardProduct/RollbackOperation [POST]
// Operation ID: StandardProduct/RollbackOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/StandardProduct/RollbackOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
