<?php
// Auto-generated endpoint: /Integrations/GetJoinedDevices [GET]
// Operation ID: Integrations/GetJoinedDevices

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Integrations/GetJoinedDevices');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
