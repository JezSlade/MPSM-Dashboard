<?php
// Auto-generated endpoint: /Explorer/Dca4ClientVersions [GET]
// Operation ID: Explorer/Dca4ClientVersions

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/Dca4ClientVersions');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
