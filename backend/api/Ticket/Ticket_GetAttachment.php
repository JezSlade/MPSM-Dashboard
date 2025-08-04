<?php
// Auto-generated endpoint: /Ticket/GetAttachment [GET]
// Operation ID: Ticket/GetAttachment

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Ticket/GetAttachment');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
