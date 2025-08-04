<?php
// Auto-generated endpoint: /Customer/EpsonUSBCustomerId/Get [GET]
// Operation ID: Customer/EpsonUSBCustomerId/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Customer/EpsonUSBCustomerId/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
