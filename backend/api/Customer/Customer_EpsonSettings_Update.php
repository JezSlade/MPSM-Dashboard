<?php
// Auto-generated endpoint: /Customer/EpsonSettings/Update [PUT]
// Operation ID: Customer/EpsonSettings/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Customer/EpsonSettings/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
