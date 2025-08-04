<?php
// Auto-generated endpoint: /Ticket/CreateTicketChild [POST]
// Operation ID: Ticket/CreateTicketChild

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Ticket/CreateTicketChild');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
