<?php
// Auto-generated endpoint: /Explorer/GetExplorerDatas [GET]
// Operation ID: Explorer/GetExplorerDatas

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/GetExplorerDatas');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
