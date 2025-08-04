<?php
// Auto-generated endpoint: /AlertLimit2/Customer/GetDefault [GET]
// Operation ID: AlertLimit2/Customer/GetDefault

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/AlertLimit2/Customer/GetDefault');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
