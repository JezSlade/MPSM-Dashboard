<?php
// Auto-generated endpoint: /Ticket/GetTicketByNumber [GET]
// Operation ID: Ticket/GetTicketByNumber

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Ticket/GetTicketByNumber');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
