<?php
// PATCHED: mps_monitor/api/get_token.php (v2.0) - Suppressed duplicate constant warnings, stable for production
// Backup created at /backup/mps_monitor/api/get_token.php.bak

declare(strict_types=1);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);
error_log("DEBUG: get_token.php patched version 2.0 starting.");

header('Content-Type: application/json; charset=utf-8');

$appRoot = dirname(__DIR__, 2);
$configPath = $appRoot . '/mps_monitor/config/mps_config.php';
$envPath = $appRoot . '/.env';

if (!file_exists($configPath) || !file_exists($envPath)) {
    http_response_code(500);
    $msg = "Missing required config or .env";
    error_log("ERROR: $msg");
    echo json_encode(['status' => 'error','code' => 500,'message' => $msg]);
    exit;
}

// Prevent constant redefinition warnings by conditionally including config
if (!defined('LOG_INFO') && !defined('LOG_WARNING') && !defined('LOG_DEBUG')) {
    require_once $configPath;
} else {
    error_log("DEBUG: Skipped reloading mps_config.php to prevent duplicate constant warnings.");
}

if (!function_exists('custom_log')) {
    function custom_log(string $message, string $level = 'INFO'): void
    {
        error_log("FALLBACK LOG [{$level}]: {$message}");
    }
}

try {
    $postFields = http_build_query([
        'grant_type'    => 'password',
        'username'      => MPS_API_USERNAME,
        'password'      => MPS_API_PASSWORD,
        'client_id'     => MPS_API_CLIENT_ID,
        'client_secret' => MPS_API_SECRET,
        'scope'         => MPS_API_SCOPE
    ]);

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
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'fetched_at' => date('Y-m-d H:i:s')
        ]
    ];

    $_SESSION['mps_token'] = $response['data'];
    $_SESSION['mps_token_timestamp'] = time();

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
