<?php
// Auto-generated endpoint: /Project/GetContractFile [GET]
// Operation ID: Project/GetContractFile

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Project/GetContractFile');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
