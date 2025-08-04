<?php
// Auto-generated endpoint: /SdsConnector/GetJamcConnectors [GET]
// Operation ID: SdsConnector/GetJamcConnectors

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsConnector/GetJamcConnectors');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
