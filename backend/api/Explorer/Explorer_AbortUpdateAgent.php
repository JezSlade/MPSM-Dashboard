<?php
// Auto-generated endpoint: /Explorer/AbortUpdateAgent [PUT]
// Operation ID: Explorer/AbortUpdateAgent

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/AbortUpdateAgent');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
