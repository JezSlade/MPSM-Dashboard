<?php
// Auto-generated endpoint: /Account/ChangePassword [POST]
// Operation ID: Account/ChangePassword

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/ChangePassword');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
