<?php
// Auto-generated endpoint: /Explorer/Configuration/CreateFromTicket [POST]
// Operation ID: Explorer/Configuration/CreateFromTicket

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/Configuration/CreateFromTicket');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
