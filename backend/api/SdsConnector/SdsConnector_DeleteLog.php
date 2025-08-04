<?php
// Auto-generated endpoint: /SdsConnector/DeleteLog [DELETE]
// Operation ID: SdsConnector/DeleteLog

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/SdsConnector/DeleteLog');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
