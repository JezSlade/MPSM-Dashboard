<?php
// Auto-generated endpoint: /Project/GetDetail [GET]
// Operation ID: Project/GetDetail

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Project/GetDetail');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
