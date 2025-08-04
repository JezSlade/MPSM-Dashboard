<?php
// Auto-generated endpoint: /Integrations/ToServ/Create [POST]
// Operation ID: Integrations/ToServ/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Integrations/ToServ/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
