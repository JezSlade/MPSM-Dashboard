<?php
// PATCHED: mps_monitor/api/get_token.php (v1.1) - Fixed get_token() arguments issue
// Backup created at /backup/mps_monitor/api/get_token.php.bak

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);
error_log("DEBUG: get_token.php patched version 1.1 starting.");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Use POST.']);
    exit;
}

$appRoot = dirname(__DIR__, 2);
$configPath = $appRoot . '/mps_monitor/config/mps_config.php';
$apiFunctionsPath = $appRoot . '/mps_monitor/includes/api_functions.php';
$envPath = $appRoot . '/.env';

if (!file_exists($configPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing mps_config.php at $configPath";
    echo json_encode(['error' => $msg]);
    error_log($msg);
    exit;
}
require_once $configPath;

if (!file_exists($apiFunctionsPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing api_functions.php at $apiFunctionsPath";
    echo json_encode(['error' => $msg]);
    error_log($msg);
    exit;
}
require_once $apiFunctionsPath;

if (!file_exists($envPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing .env file at $envPath";
    echo json_encode(['error' => $msg]);
    error_log($msg);
    exit;
}

try {
    $config = parse_env_file($envPath);

    // Merge required parameters directly into config before calling get_token()
    $config['grant_type'] = 'password';
    $config['username'] = MPS_API_USERNAME;
    $config['password'] = MPS_API_PASSWORD;

    error_log("DEBUG: Requesting token with strict grant_type enforcement.");
    $tokenData = get_token($config);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('Token response missing access_token');
    }

    $response = [
        'access_token' => $tokenData['access_token'],
        'token_type' => $tokenData['token_type'] ?? 'bearer',
        'expires_in' => $tokenData['expires_in'] ?? 3600,
        'refresh_token' => $tokenData['refresh_token'] ?? null
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Throwable $e) {
    http_response_code(500);
    $msg = "ERROR: " . $e->getMessage();
    error_log($msg);
    echo json_encode([
        'error' => $msg,
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
