<?php
// Auto-generated endpoint: /Explorer/UpdateDca4Client [PUT]
// Operation ID: Explorer/UpdateDca4Client

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/UpdateDca4Client');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
