<?php
// public/api-proxy.php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';
require_once __DIR__ . '/src/ApiClient.php';

header('Content-Type: application/json');

$method = strtoupper($_REQUEST['method'] ?? 'GET');
$path   = trim($_REQUEST['path']   ?? '', '/');

// Token
$client = new ApiClient();
$token  = $client->getAccessToken();
if (!$token) {
    http_response_code(500);
    echo json_encode(['error'=>'Token failure']);
    exit;
}

if ($method==='GET') {
    $url = rtrim(API_BASE_URL,'/').'/'.$path;
    DebugPanel::log("Proxy GET $url");
    $opts=['http'=>['method'=>'GET','header'=>"Authorization: Bearer $token\r\nAccept: application/json\r\n",'ignore_errors'=>true]];
    $resp=@file_get_contents($url,false,stream_context_create($opts));
    if (!$resp) { http_response_code(502); echo json_encode(['error'=>'Upstream GET failed']); exit; }
    echo $resp;
    exit;
}

// POST fallback
$raw = file_get_contents('php://input');
$data = json_decode($raw,true);
if (json_last_error()!==JSON_ERROR_NONE) {
    DebugPanel::log("POST JSON parse error: ".json_last_error_msg());
    $data = [];
}
$response = $client->postJson($path,$token,$data);
echo json_encode($response);
