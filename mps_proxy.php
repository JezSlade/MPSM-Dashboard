<?php
header('Content-Type: application/json');
require __DIR__ . '/core/bootstrap.php';
require_login();

$endpoint = $_GET['endpoint'] ?? '';
if (!$endpoint) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing endpoint']);
    exit;
}

$params = $_GET;
unset($params['endpoint']);

try {
    $data = fetch_mps_api($endpoint, $params);
    echo json_encode($data);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
