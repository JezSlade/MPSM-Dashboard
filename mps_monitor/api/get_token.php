<?php
// PATCHED: /mps_monitor/api/get_token.php
// Version: v2.2 - Config Include Assurance & Full Cohesion Audit
// ------------------------------------------------------
// • Ensures mps_config.php is always properly included.
// • Audited to work cohesively with global_token_handler.php v3.3 & all widgets.


declare(strict_types=1);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);

header('Content-Type: application/json; charset=utf-8');

// ✅ Ensure configuration is always loaded
$configPath = dirname(__DIR__) . '/config/mps_config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    $msg = "Missing required config at $configPath";
    error_log("ERROR: $msg");
    echo json_encode(['status' => 'error', 'code' => 500, 'message' => $msg]);
    exit;
}
require_once $configPath;

try {
    // ✅ Handle refresh vs password grant
    $useRefresh = isset($_SESSION['mps_token']['refresh_token']) && isset($_GET['refresh']) && $_GET['refresh'] === 'true';

    if ($useRefresh) {
        $postFields = http_build_query([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $_SESSION['mps_token']['refresh_token'],
            'client_id'     => MPS_API_CLIENT_ID,
            'client_secret' => MPS_API_SECRET
        ]);
        error_log("DEBUG: Using refresh token grant.");
    } else {
        $postFields = http_build_query([
            'grant_type'    => 'password',
            'username'      => MPS_API_USERNAME,
            'password'      => MPS_API_PASSWORD,
            'client_id'     => MPS_API_CLIENT_ID,
            'client_secret' => MPS_API_SECRET,
            'scope'         => MPS_API_SCOPE
        ]);
        error_log("DEBUG: Using password grant.");
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => MPS_TOKEN_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);

    $rawResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    error_log("DEBUG: Raw token HTTP({$httpCode}) response: {$rawResponse}");

    if ($curlErr) {
        throw new Exception("cURL error: {$curlErr}");
    }

    $tokenData = json_decode($rawResponse, true);

    if (!is_array($tokenData) || !isset($tokenData['access_token'])) {
        throw new Exception("Invalid token response: " . $rawResponse);
    }

    $response = [
        'status' => 'success',
        'code' => $httpCode,
        'data' => [
            'access_token' => $tokenData['access_token'],
            'token_type' => $tokenData['token_type'] ?? 'bearer',
            'expires_in' => $tokenData['expires_in'] ?? 3600,
            'refresh_token' => $tokenData['refresh_token'] ?? ($_SESSION['mps_token']['refresh_token'] ?? null),
            'fetched_at' => date('Y-m-d H:i:s')
        ]
    ];

    // ✅ Store token for global handler & widgets
    $_SESSION['mps_token'] = $response['data'];
    $_SESSION['mps_token_timestamp'] = time();
    setcookie('mps_token', json_encode($response['data']), time() + $response['data']['expires_in'], '/');

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    $msg = "ERROR: " . $e->getMessage();
    error_log($msg);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => $msg,
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
?>
