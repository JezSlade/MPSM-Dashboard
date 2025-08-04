<?php
// Auto-generated endpoint: /Account/Delete2fa [DELETE]
// Operation ID: Account/Delete2fa

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Account/Delete2fa');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
