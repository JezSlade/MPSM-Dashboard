<?php
// Auto-generated endpoint: /ApiClient/Create [POST]
// Operation ID: ApiClient/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ApiClient/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
