<?php
// USAGE: This script is auto-called by get_valid_mps_token() for refreshing tokens.
// Do not call this directly. Use global_token_handler.php instead.

// PATCHED: get_token.php v2.6 - Clean Final Version
// ------------------------------------------------------
// • Removed all logging suppression hacks.
// • Leaves config as-is; token works correctly.

printf(format: "This is a clean final version of get_token.php.\n");

/*
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logger.php';
declare(strict_types=1);
session_start();

// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

header('Content-Type: application/json; charset=utf-8');

$configPath = dirname(__DIR__) . '/config/mps_config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error','code' => 500,'message' => "Missing required config at $configPath"]);
    exit;
}
require_once $configPath;

try {
    $useRefresh = isset($_SESSION['mps_token']['refresh_token']) && ($_GET['refresh'] ?? '') === 'true';

    if ($useRefresh) {
        $postFields = http_build_query([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $_SESSION['mps_token']['refresh_token'],
            'client_id'     => MPS_API_CLIENT_ID,
            'client_secret' => MPS_API_SECRET
        ]);
    } else {
        $postFields = http_build_query([
            'grant_type'    => 'password',
            'username'      => MPS_API_USERNAME,
            'password'      => MPS_API_PASSWORD,
            'client_id'     => MPS_API_CLIENT_ID,
            'client_secret' => MPS_API_SECRET,
            'scope'         => MPS_API_SCOPE
        ]);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => MPS_TOKEN_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);

    $rawResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

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

    $_SESSION['mps_token'] = $response['data'];
    $_SESSION['mps_token_timestamp'] = time();

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => "ERROR: " . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
?>
