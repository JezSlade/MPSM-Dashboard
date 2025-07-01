<?php // mps_monitor/api/get_token.php
declare(strict_types=1);

// --- AGGRESSIVE DEBUGGING START ---
// These lines are added at the very top to force error display
// and logging, in case errors occur before mps_config.php is fully loaded.
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', '1'); // Display errors directly in the browser
ini_set('log_errors', '1'); // Ensure errors are logged to the PHP error log
ini_set('error_log', dirname(__DIR__, 2) . '/logs/php_error_early.log'); // A dedicated log for early errors
// --- AGGRESSIVE DEBUGGING END ---

// CRITICAL: Start output buffering as the very first executable line.
// This captures all output and prevents "headers already sent" errors,
// ensuring no stray characters precede the JSON response.
ob_start();

// Define the application root path.
// Assuming .env is in the project root, two levels up from 'api' folder.
$appRoot = dirname(__DIR__, 2);

// Include necessary configuration and API utility functions.
// mps_config.php provides constants like MPS_TOKEN_URL and the custom_log function.
// This file also sets up more comprehensive logging and error reporting.
require_once $appRoot . '/mps_monitor/config/mps_config.php';
// api_functions.php provides parse_env_file and the standalone get_token function.
require_once $appRoot . '/mps_monitor/includes/api_functions.php';

// Set the Content-Type header to application/json for the response.
header('Content-Type: application/json');

$response = []; // Initialize response array

try {
    // Load environment variables from the .env file.
    $config = parse_env_file($appRoot . '/.env');

    // Attempt to get the token using the standalone get_token function.
    $token = get_token($config);
    // If successful, prepare the response with the access token.
    $response = ['access_token' => $token];
    custom_log('Token debug endpoint: Token retrieved successfully.', 'INFO');
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
    custom_log('Token debug endpoint: Failed to get token: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), 'ERROR');
} finally {
    // Ensure the final response is always JSON.
    echo json_encode($response);

    // CRITICAL: Forcefully clean and flush all output buffers and terminate script execution.
    // This prevents any accidental trailing output that could corrupt the JSON response.
    while (ob_get_level() > 0) { ob_end_clean(); }
    die();
}
?>
