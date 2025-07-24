<?php
// PATCHED: mps_monitor/api/get_token.php (v1.5) - Auto-call token on direct GET
// Backup created at /backup/mps_monitor/api/get_token.php.bak

declare(strict_types=1);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);
error_log("DEBUG: get_token.php patched version 1.5 starting.");

header('Content-Type: application/json; charset=utf-8');

$appRoot = dirname(__DIR__, 2);
$configPath = $appRoot . '/mps_monitor/config/mps_config.php';
$apiFunctionsPath = $appRoot . '/mps_monitor/includes/api_functions.php';
$envPath = $appRoot . '/.env';

$requiredFiles = [
    'config' => $configPath,
    'api_functions' => $apiFunctionsPath,
    'env' => $envPath
];

foreach ($requiredFiles as $key => $path) {
    if (!file_exists($path)) {
        http_response_code(500);
        $msg = "Missing required $key file at $path";
        error_log("ERROR: $msg");
        echo json_encode([
            'status' => 'error',
            'code' => 500,
            'message' => $msg
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

require_once $configPath;
require_once $apiFunctionsPath;

try {
    $config = parse_env_file($envPath);

    $config['grant_type'] = 'password';
    $config['username'] = MPS_API_USERNAME;
    $config['password'] = MPS_API_PASSWORD;

    error_log("DEBUG: Requesting token with strict grant_type enforcement.");
    $tokenData = get_token($config);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('Token response missing access_token');
    }

    $response = [
        'status' => 'success',
        'code' => 200,
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
