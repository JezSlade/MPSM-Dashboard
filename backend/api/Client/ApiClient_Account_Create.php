<?php
// Auto-generated endpoint: /ApiClient/Account/Create [POST]
// Operation ID: ApiClient/Account/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ApiClient/Account/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
