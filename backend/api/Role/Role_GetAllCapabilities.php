<?php
// Auto-generated endpoint: /Role/GetAllCapabilities [GET]
// Operation ID: Role/GetAllCapabilities

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Role/GetAllCapabilities');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
