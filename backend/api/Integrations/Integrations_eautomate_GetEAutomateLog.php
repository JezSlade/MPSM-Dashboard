<?php
// Auto-generated endpoint: /Integrations/eautomate/GetEAutomateLog [GET]
// Operation ID: Integrations/eautomate/GetEAutomateLog

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Integrations/eautomate/GetEAutomateLog');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
