<?php
// Auto-generated endpoint: /Account/Enable2faForProfile [POST]
// Operation ID: Account/Enable2faForProfile

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/Enable2faForProfile');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
