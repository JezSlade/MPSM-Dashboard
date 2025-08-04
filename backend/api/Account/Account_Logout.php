<?php
// Auto-generated endpoint: /Account/Logout [POST]
// Operation ID: Account/Logout

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/Logout');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
