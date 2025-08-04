<?php // mps_monitor/includes/api_bootstrap.php
declare(strict_types=1);

// CRITICAL: Start output buffering as the very first executable line.
// This captures all output and prevents "headers already sent" errors,
// ensuring no stray characters precede the JSON response.
ob_start();

// Define the root path of the application.
// Assuming .env is in the project root, two levels up from 'includes' folder.
$appRoot = dirname(__DIR__, 2);

// Include core configuration and helper files.
// mps_config.php defines constants and the custom_log function.
require_once $appRoot . '/mps_monitor/config/mps_config.php';
// api_functions.php provides parse_env_file and standalone get_token/call_api.
require_once $appRoot . '/mps_monitor/includes/api_functions.php';
// CacheHelper.php provides the caching mechanism for API responses.
require_once $appRoot . '/mps_monitor/helpers/CacheHelper.php';
// MPSMonitorClient.php provides the main API client with token management.
require_once $appRoot . '/mps_monitor/src/MPSMonitorClient.php';

/**
 * API Bootstrapping and Routing
 * This file acts as a standardized entry point for all internal PHP API endpoints.
 * It ensures consistent behavior, error handling, and caching for API calls.
 *
 * It expects the following variables to be set by the including endpoint script (e.g., get_customers.php):
 * - $method (string, e.g., 'POST', 'GET') - The HTTP method for the external API call.
 * - $path (string) - The specific path for the external MPS Monitor API endpoint.
 * - $useCache (bool, optional) - Whether to cache the API response. Defaults to false.
 * - $cacheTtl (int, optional) - TTL for this specific cache entry in seconds. Defaults to DEFAULT_CACHE_TTL.
 * - $requiredFields (array, optional) - An array of field names required in the input payload.
 */

// Load environment variables from .env file.
$config = parse_env_file($appRoot . '/.env');

// Initialize CacheHelper for API response caching.
// This cache is separate from the token cache managed by MPSMonitorClient.
$apiResponseCacheDir = $appRoot . '/cache/api_responses';
$cacheHelper = new CacheHelper($apiResponseCacheDir, DEFAULT_CACHE_TTL);

// Set default values for variables if not provided by the including script.
$method = $method ?? 'POST';
$path = $path ?? '';
$useCache = $useCache ?? false;
$cacheTtl = $cacheTtl ?? DEFAULT_CACHE_TTL;
$requiredFields = $requiredFields ?? [];

// Set Content-Type header for JSON responses before any output.
header('Content-Type: application/json');

// Read raw input body for POST/PUT requests with JSON payload.
// If no JSON body, it defaults to an empty array.
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Enforce required fields from the incoming JSON payload.
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        http_response_code(400); // Set HTTP status code to 400 Bad Request
        echo json_encode(['error' => "Missing or empty required field: " . $field]);
        custom_log("Missing required field: " . $field . " for path: " . $path, 'WARNING');
        // Forcefully clean and flush all output buffers and terminate script execution.
        while (ob_get_level() > 0) { ob_end_clean(); }
        die();
    }
}

$response = [];
$cacheKey = null;

try {
    // If caching is enabled, try to retrieve the response from cache first.
    if ($useCache) {
        // Generate a unique cache key based on path, method, and input data.
        $cacheKey = 'api_response_' . md5($path . $method . json_encode($input));
        $cachedResponse = $cacheHelper->get($cacheKey);
        if ($cachedResponse) {
            custom_log('API response retrieved from cache for path: ' . $path, 'INFO');
            echo json_encode($cachedResponse);
            // Forcefully clean and flush all output buffers and terminate.
            while (ob_get_level() > 0) { ob_end_clean(); }
            die();
        }
    }

    // Initialize the MPSMonitorClient.
    // This client handles its own token acquisition and refresh, potentially using its own cache.
    $client = new MPSMonitorClient();

    // Make the actual API call using the MPSMonitorClient.
    // The client's callApi method does not handle response caching itself; api_bootstrap does.
    $response = $client->callApi($path, $method, $input);

    // If caching is enabled and the response was not served from cache, store it.
    if ($useCache && $cacheKey) {
        $cacheHelper->set($cacheKey, $response, $cacheTtl);
        custom_log('API response stored in cache for path: ' . $path, 'INFO');
    }

} catch (Exception $e) {
    // Catch any exceptions thrown during the API call or token acquisition.
    http_response_code(500); // Set HTTP status code to 500 Internal Server Error
    $response = ['error' => 'An internal server error occurred: ' . $e->getMessage()];
    custom_log('Exception in api_bootstrap.php for path ' . $path . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), 'ERROR');
}

// Ensure the final response is always JSON.
echo json_encode($response);

// CRITICAL: Forcefully clean and flush all output buffers and terminate script execution.
// This prevents any accidental trailing output that could corrupt the JSON response.
while (ob_get_level() > 0) { ob_end_clean(); }
die();
?>
