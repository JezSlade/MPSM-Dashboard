<?php
// mps_monitor/api/get_token.php
declare(strict_types=1);

// Debug output always visible
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);
error_log("DEBUG: get_token.php starting.");

// DO NOT use output buffering
echo "DEBUG: Script started\n";

// Paths
$appRoot = dirname(__DIR__, 2);
$configPath = $appRoot . '/mps_monitor/config/mps_config.php';
$apiFunctionsPath = $appRoot . '/mps_monitor/includes/api_functions.php';
$envPath = $appRoot . '/.env';

// Validate includes
if (!file_exists($configPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing mps_config.php at $configPath";
    echo "$msg\n"; error_log($msg); exit;
}
require_once $configPath;

if (!file_exists($apiFunctionsPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing api_functions.php at $apiFunctionsPath";
    echo "$msg\n"; error_log($msg); exit;
}
require_once $apiFunctionsPath;

// Validate .env
if (!file_exists($envPath)) {
    http_response_code(500);
    $msg = "ERROR: Missing .env file at $envPath";
    echo "$msg\n"; error_log($msg); exit;
}

// Parse and request token
try {
    echo "DEBUG: Parsing .env file...\n";
    $config = parse_env_file($envPath);
    echo "DEBUG: .env parsed. Keys: " . implode(', ', array_keys($config)) . "\n";

    echo "DEBUG: Requesting token...\n";
    $token = get_token($config);
    echo "SUCCESS: Token retrieved.\n";

    header('Content-Type: application/json');
    echo json_encode(['access_token' => $token]);

} catch (Throwable $e) {
    http_response_code(500);
    $msg = "ERROR: " . $e->getMessage();
    echo "$msg\n";
    error_log($msg);
    echo json_encode([
        'error' => $msg,
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
