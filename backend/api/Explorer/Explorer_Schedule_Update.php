<?php
// Auto-generated endpoint: /Explorer/Schedule/Update [POST]
// Operation ID: Explorer/Schedule/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/Schedule/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
