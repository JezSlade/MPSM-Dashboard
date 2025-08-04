<?php
// Auto-generated endpoint: /SdsConnector/DownloadLog [GET]
// Operation ID: SdsConnector/DownloadLog

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsConnector/DownloadLog');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
