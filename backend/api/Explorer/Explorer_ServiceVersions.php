<?php
// Auto-generated endpoint: /Explorer/ServiceVersions [GET]
// Operation ID: Explorer/ServiceVersions

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/ServiceVersions');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
