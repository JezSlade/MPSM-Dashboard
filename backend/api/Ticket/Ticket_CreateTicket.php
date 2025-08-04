<?php
// Auto-generated endpoint: /Ticket/CreateTicket [POST]
// Operation ID: Ticket/CreateTicket

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Ticket/CreateTicket');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
