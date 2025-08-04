<?php
// Auto-generated endpoint: /DealerNotification/RemoveMailAddressFromCustomers [PUT]
// Operation ID: DealerNotification/RemoveMailAddressFromCustomers

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/DealerNotification/RemoveMailAddressFromCustomers');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
