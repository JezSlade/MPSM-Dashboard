<?php
// Auto-generated endpoint: /SupplyAlert/MassiveUpdate [POST]
// Operation ID: SupplyAlert/MassiveUpdate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SupplyAlert/MassiveUpdate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
