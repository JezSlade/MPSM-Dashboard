<?php
// Auto-generated endpoint: /Explorer/Staging/Activate [POST]
// Operation ID: Explorer/Staging/Activate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/Staging/Activate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
