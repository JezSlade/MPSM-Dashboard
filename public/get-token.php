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
try {
    $data = $client->getTokenData();
    if (empty($data['access_token'])) {
        throw new Exception('No access_token in response');
    }
    echo json_encode([
        'access_token' => $data['access_token'],
        'expires_in'   => $data['expires_in'] ?? 3600,
        'token_type'   => $data['token_type'] ?? 'Bearer'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Token fetch error: '.$e->getMessage()]);
}
