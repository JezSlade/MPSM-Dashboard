<?php // mps_monitor/api/get_token.php
declare(strict_types=1);

// --- AGGRESSIVE DEBUGGING START ---
// These lines are added at the very top to force error display
// and logging, in case errors occur before mps_config.php is fully loaded.
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', '1'); // Display errors directly in the browser
ini_set('log_errors', '1'); // Ensure errors are logged to the PHP error log
// Use a dedicated log for early errors, ensure it's writable.
$earlyLogPath = dirname(__DIR__, 2) . '/logs/php_error_early.log';
ini_set('error_log', $earlyLogPath);

// Attempt to write a very early log entry to confirm logging setup
error_log("DEBUG: get_token.php script started execution. Early log path: " . $earlyLogPath);
// --- AGGRESSIVE DEBUGGING END ---

// CRITICAL: Start output buffering as the very first executable line.
// This captures all output and prevents "headers already sent" errors,
// ensuring no stray characters precede the JSON response.
ob_start();

// Define the application root path.
// Assuming .env is in the project root, two levels up from 'api' folder.
$appRoot = dirname(__DIR__, 2);

// --- Path Debugging ---
// Check if essential files exist before attempting to include them.
$configPath = $appRoot . '/mps_monitor/config/mps_config.php';
$apiFunctionsPath = $appRoot . '/mps_monitor/includes/api_functions.php';

if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Critical file not found: mps_config.php at ' . $configPath]);
    error_log('Critical file not found: mps_config.php at ' . $configPath);
    ob_end_clean();
    die();
}
require_once $configPath;
custom_log('DEBUG: mps_config.php included successfully.', 'DEBUG');


if (!file_exists($apiFunctionsPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Critical file not found: api_functions.php at ' . $apiFunctionsPath]);
    error_log('Critical file not found: api_functions.php at ' . $apiFunctionsPath);
    ob_end_clean();
    die();
}
require_once $apiFunctionsPath;
custom_log('DEBUG: api_functions.php included successfully.', 'DEBUG');

// Set the Content-Type header to application/json for the response.
header('Content-Type: application/json');

$response = []; // Initialize response array

try {
    custom_log('DEBUG: Attempting to parse .env file.', 'DEBUG');
    // Load environment variables from the .env file.
    $config = parse_env_file($appRoot . '/.env');
    custom_log('DEBUG: .env file parsed. Config keys: ' . implode(', ', array_keys($config)), 'DEBUG');


    custom_log('DEBUG: Attempting to get token using get_token function.', 'DEBUG');
    // Attempt to get the token using the standalone get_token function.
    $token = get_token($config);
    custom_log('DEBUG: Token successfully received.', 'DEBUG');

    // If successful, prepare the response with the access token.
    $response = ['access_token' => $token];
    custom_log('INFO: Token debug endpoint: Token retrieved successfully.', 'INFO');
} catch (Throwable $e) { // Catch Throwable to also catch fatal errors (e.g., parse errors)
    // If an exception occurs (e.g., token acquisition fails), set HTTP status code to 500.
    http_response_code(500); // Internal Server Error
    // Prepare an error response with the exception message.
    $response = [
        'error' => 'Failed to get token: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString() // Include trace for detailed debugging
    ];
    custom_log('ERROR: Token debug endpoint: Failed to get token: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), 'ERROR');
} finally {
    // Ensure the final response is always JSON.
    echo json_encode($response);

    // CRITICAL: Forcefully clean and flush all output buffers and terminate script execution.
    // This prevents any accidental trailing output that could corrupt the JSON response.
    while (ob_get_level() > 0) { ob_end_clean(); }
    die();
}
?>
