<?php
// Auto-generated endpoint: /Explorer/Hostname/Delete [DELETE]
// Operation ID: Explorer/Hostname/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Explorer/Hostname/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
