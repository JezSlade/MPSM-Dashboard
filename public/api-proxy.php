<?php
// public/api-proxy.php
// -------------------------------------
// Proxy GET/POST requests to MPSM API using ApiClient.
// -------------------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';
require_once __DIR__ . '/src/ApiClient.php';

header('Content-Type: application/json');

$method = strtoupper($_REQUEST['method'] ?? 'GET');
$path   = trim($_REQUEST['path'] ?? '', '/');

// Acquire token
$client = new ApiClient();
$token  = $client->getAccessToken();
if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to acquire token']);
    exit;
}

// Handle GET
if ($method === 'GET') {
    $url = rtrim(API_BASE_URL, '/') . '/' . $path;
    DebugPanel::log("Proxy GET $url");
    $opts = ['http' => [
        'method'        => 'GET',
        'header'        => "Authorization: Bearer $token\r\nAccept: application/json\r\n",
        'ignore_errors' => true
    ]];
    $resp = @file_get_contents($url, false, stream_context_create($opts));
    if ($resp === false) {
        http_response_code(502);
        echo json_encode(['error' => 'Upstream GET failed']);
        exit;
    }
    echo $resp;
    exit;
}

// Handle POST (and other methods)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];
$response = $client->postJson($path, $token, $data);
echo json_encode($response);
