<?php
// Auto-generated endpoint: /SdsConnector/Register [POST]
// Operation ID: SdsConnector/Register

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsConnector/Register');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
