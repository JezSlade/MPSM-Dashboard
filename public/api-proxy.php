<?php
// public/api-proxy.php
// --------------------
// Unified proxy that accepts { Url, Request, Method } JSON.
// --------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';
require_once __DIR__ . '/src/ApiClient.php';

header('Content-Type: application/json');

// Read raw JSON body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    DebugPanel::log("Proxy JSON parse error: ".json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error'=>'Invalid JSON']);
    exit;
}

// Derive path, method, and payload from SDK JSON or fallback to legacy query-string
if (isset($data['Url'], $data['Request'])) {
    $path    = trim($data['Url'], '/');
    $method  = strtoupper($data['Method'] ?? 'GET');
    $payload = $data['Request'];
} else {
    // Legacy support: query params + raw body as payload
    $method  = strtoupper($_REQUEST['method'] ?? 'GET');
    $path    = trim($_REQUEST['path']   ?? '', '/');
    $payload = $data;
}

// Acquire OAuth2 token
$client = new ApiClient();
$token  = $client->getAccessToken();
if (!$token) {
    http_response_code(500);
    echo json_encode(['error'=>'Token failure']);
    exit;
}

if ($method === 'GET') {
    // Build URL with any query parameters
    $url = rtrim(API_BASE_URL, '/').'/'.$path;
    if (!empty($payload) && is_array($payload)) {
        $url .= '?'.http_build_query($payload);
    }
    DebugPanel::log("Proxy GET $url");
    $opts = ['http'=>[
        'method'        => 'GET',
        'header'        => "Authorization: Bearer $token\r\nAccept: application/json\r\n",
        'ignore_errors' => true
    ]];
    $resp = @file_get_contents($url, false, stream_context_create($opts));
    if ($resp === false) {
        http_response_code(502);
        echo json_encode(['error'=>'Upstream GET failed']);
        exit;
    }
    echo $resp;
    exit;
}

// POST/other methods
DebugPanel::log("Proxy $method /$path");
$response = $client->postJson($path, $token, $payload);
echo json_encode($response);
