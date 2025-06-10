<?php
// public/get-token.php
// -------------------------------------
// AJAX endpoint to return { access_token: "…" }
// or { error: "…" } on failure.
// -------------------------------------

// Full PHP error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/DebugPanel.php';
require_once __DIR__ . '/../src/ApiClient.php';

header('Content-Type: application/json');

$client = new ApiClient();
$token  = $client->getAccessToken();

if (empty($token)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to acquire access token']);
    exit;
}

echo json_encode(['access_token' => $token]);
