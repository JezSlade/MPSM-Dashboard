<?php
// Auto-generated endpoint: /Customer/AdvancedOptions/Get [GET]
// Operation ID: Customer/AdvancedOptions/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Customer/AdvancedOptions/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
