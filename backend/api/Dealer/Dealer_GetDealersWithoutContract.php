<?php
// Auto-generated endpoint: /Dealer/GetDealersWithoutContract [POST]
// Operation ID: Dealer/GetDealersWithoutContract

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Dealer/GetDealersWithoutContract');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
