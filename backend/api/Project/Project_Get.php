<?php
// Auto-generated endpoint: /Project/Get [POST]
// Operation ID: Project/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Project/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
