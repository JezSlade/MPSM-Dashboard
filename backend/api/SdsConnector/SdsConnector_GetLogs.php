<?php
// Auto-generated endpoint: /SdsConnector/GetLogs [GET]
// Operation ID: SdsConnector/GetLogs

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsConnector/GetLogs');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
