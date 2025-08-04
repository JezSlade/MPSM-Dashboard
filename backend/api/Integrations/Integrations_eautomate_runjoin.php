<?php
// Auto-generated endpoint: /Integrations/eautomate/runjoin [GET]
// Operation ID: Integrations/eautomate/runjoin

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Integrations/eautomate/runjoin');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
