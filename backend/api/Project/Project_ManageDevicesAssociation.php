<?php
// Auto-generated endpoint: /Project/ManageDevicesAssociation [PUT]
// Operation ID: Project/ManageDevicesAssociation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Project/ManageDevicesAssociation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
