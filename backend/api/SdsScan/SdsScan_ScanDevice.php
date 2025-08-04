<?php
// Auto-generated endpoint: /SdsScan/ScanDevice [GET]
// Operation ID: SdsScan/ScanDevice

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsScan/ScanDevice');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
