<?php
// Auto-generated endpoint: /Explorer/GetExplorerSetupLink [GET]
// Operation ID: Explorer/GetExplorerSetupLink

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/GetExplorerSetupLink');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
