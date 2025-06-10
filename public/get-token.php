<?php
// public/get-token.php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';
require_once __DIR__ . '/src/ApiClient.php';

header('Content-Type: application/json');

$client = new ApiClient();
$data   = $client->getTokenData();
if (empty($data['access_token'])) {
    http_response_code(500);
    echo json_encode(['error'=>'Failed to acquire token']);
    exit;
}

// Return raw token data including expires_in
echo json_encode([
    'access_token' => $data['access_token'],
    'expires_in'   => $data['expires_in'] ?? 3600,
    'token_type'   => $data['token_type'] ?? 'Bearer'
]);
