<?php
// Auto-generated endpoint: /Device/GetDetailedInformations [POST]
// Operation ID: Device/GetDetailedInformations

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Device/GetDetailedInformations');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
