<?php
// Auto-generated endpoint: /Analytics/GetReportFileResult [GET]
// Operation ID: Analytics/GetReportFileResult

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Analytics/GetReportFileResult');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
