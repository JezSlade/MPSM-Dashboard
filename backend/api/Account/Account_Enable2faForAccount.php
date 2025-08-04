<?php
// Auto-generated endpoint: /Account/Enable2faForAccount [POST]
// Operation ID: Account/Enable2faForAccount

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/Enable2faForAccount');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
