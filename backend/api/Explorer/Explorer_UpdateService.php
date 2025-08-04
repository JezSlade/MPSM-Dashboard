<?php
// Auto-generated endpoint: /Explorer/UpdateService [PUT]
// Operation ID: Explorer/UpdateService

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/UpdateService');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
