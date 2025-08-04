<?php
// Auto-generated endpoint: /Integrations/Update [PUT]
// Operation ID: Integrations/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Integrations/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
