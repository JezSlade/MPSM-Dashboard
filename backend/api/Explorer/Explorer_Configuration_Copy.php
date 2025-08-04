<?php
// Auto-generated endpoint: /Explorer/Configuration/Copy [PUT]
// Operation ID: Explorer/Configuration/Copy

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/Configuration/Copy');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
