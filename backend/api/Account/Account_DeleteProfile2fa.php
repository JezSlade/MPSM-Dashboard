<?php
// Auto-generated endpoint: /Account/DeleteProfile2fa [DELETE]
// Operation ID: Account/DeleteProfile2fa

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Account/DeleteProfile2fa');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
