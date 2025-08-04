<?php
// Auto-generated endpoint: /Integrations/VantageOnline/Create [POST]
// Operation ID: Integrations/VantageOnline/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Integrations/VantageOnline/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
