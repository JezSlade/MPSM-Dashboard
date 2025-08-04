<?php
// Auto-generated endpoint: /Office/OfficeFloor/SavePin [POST]
// Operation ID: Office/OfficeFloor/SavePin

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Office/OfficeFloor/SavePin');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
