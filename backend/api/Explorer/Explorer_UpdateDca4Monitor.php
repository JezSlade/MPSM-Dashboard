<?php
// Auto-generated endpoint: /Explorer/UpdateDca4Monitor [PUT]
// Operation ID: Explorer/UpdateDca4Monitor

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/UpdateDca4Monitor');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
