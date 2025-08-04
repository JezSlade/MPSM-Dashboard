<?php
// Auto-generated endpoint: /SdsDevice/PerformPrintQualityDiagnostics [POST]
// Operation ID: SdsDevice/PerformPrintQualityDiagnostics

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsDevice/PerformPrintQualityDiagnostics');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
