<?php // mps_monitor/api/get_token.php
declare(strict_types=1);

/**
 * API Endpoint: get_token.php
 * This is a debug/test endpoint to directly acquire and return an OAuth access token.
 * It uses the standalone get_token function from api_functions.php.
 * This endpoint is useful for verifying token acquisition independently of other API calls.
 */

// Define the application root path.
// Assuming .env is in the project root, two levels up from 'api' folder.
$appRoot = dirname(__DIR__, 2);

// Include necessary configuration and API utility functions.
// mps_config.php provides constants like MPS_TOKEN_URL and the custom_log function.
require_once $appRoot . '/mps_monitor/config/mps_config.php';
// api_functions.php provides parse_env_file and the standalone get_token function.
require_once $appRoot . '/mps_monitor/includes/api_functions.php';

// Start output buffering to prevent "headers already sent" errors.
ob_start();

// Load environment variables from the .env file.
$config = parse_env_file($appRoot . '/.env');

// Set the Content-Type header to application/json for the response.
header('Content-Type: application/json');

$response = []; // Initialize response array

try {
    // Attempt to get the token using the standalone get_token function.
    $token = get_token($config);
    // If successful, prepare the response with the access token.
    $response = ['access_token' => $token];
    custom_log('Token debug endpoint: Token retrieved successfully.', 'INFO');
} catch (Exception $e) {
    // If an exception occurs (e.g., token acquisition fails), set HTTP status code to 500.
    http_response_code(500); // Internal Server Error
    // Prepare an error response with the exception message.
    $response = ['error' => 'Failed to get token: ' . $e->getMessage()];
    custom_log('Token debug endpoint: Failed to get token: ' . $e->getMessage(), 'ERROR');
}

// Encode the response array to JSON and echo it.
echo json_encode($response);

// CRITICAL: Forcefully clean and flush all output buffers and terminate script execution.
// This ensures no accidental trailing output that could corrupt the JSON response.
while (ob_get_level() > 0) { ob_end_clean(); }
die();
?>
