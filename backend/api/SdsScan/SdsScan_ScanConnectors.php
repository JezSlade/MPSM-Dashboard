<?php
// Auto-generated endpoint: /SdsScan/ScanConnectors [POST]
// Operation ID: SdsScan/ScanConnectors

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsScan/ScanConnectors');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
