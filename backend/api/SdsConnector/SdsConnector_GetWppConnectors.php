<?php
// Auto-generated endpoint: /SdsConnector/GetWppConnectors [GET]
// Operation ID: SdsConnector/GetWppConnectors

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsConnector/GetWppConnectors');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
