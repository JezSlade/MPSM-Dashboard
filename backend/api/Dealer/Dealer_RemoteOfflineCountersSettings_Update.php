<?php
// Auto-generated endpoint: /Dealer/RemoteOfflineCountersSettings/Update [PUT]
// Operation ID: Dealer/RemoteOfflineCountersSettings/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/RemoteOfflineCountersSettings/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
