<?php
// Auto-generated endpoint: /WhiteLabel/UpdateDcaLicenses [PUT]
// Operation ID: WhiteLabel/UpdateDcaLicenses

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/WhiteLabel/UpdateDcaLicenses');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
