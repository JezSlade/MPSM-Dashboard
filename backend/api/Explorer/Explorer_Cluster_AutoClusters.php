<?php
// Auto-generated endpoint: /Explorer/Cluster/AutoClusters [GET]
// Operation ID: Explorer/Cluster/AutoClusters

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/Cluster/AutoClusters');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
