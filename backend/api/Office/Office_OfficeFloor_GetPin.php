<?php
// Auto-generated endpoint: /Office/OfficeFloor/GetPin [GET]
// Operation ID: Office/OfficeFloor/GetPin

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Office/OfficeFloor/GetPin');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
