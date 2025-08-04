<?php
// Auto-generated endpoint: /Ticket/GetTicket [GET]
// Operation ID: Ticket/GetTicket

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Ticket/GetTicket');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
