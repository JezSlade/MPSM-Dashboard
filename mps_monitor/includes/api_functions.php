<?php // mps_monitor/includes/api_functions.php
declare(strict_types=1);

// This file contains utility functions for the API integration,
// specifically for parsing .env files and a standalone token acquisition function.
// Note: MPSMonitorClient.php now handles token acquisition internally for API calls.
// The get_token function here is provided for direct debugging or specific use cases.

/**
 * Parses a .env file and returns its contents as an associative array.
 *
 * @param string $filePath The full path to the .env file.
 * @return array An associative array of environment variables.
 */
function parse_env_file(string $filePath): array
{
    $env = [];
    if (!file_exists($filePath)) {
        custom_log("Error: .env file not found at " . $filePath, 'ERROR');
        return $env;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments (lines starting with #)
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        // Parse key=value pairs
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes from value (single or double quotes)
            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = trim($value, '"');
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = trim($value, "'");
            }
            $env[$key] = $value;
        }
    }
    custom_log('Parsed .env file.', 'DEBUG');
    return $env;
}

/**
 * Fetches an OAuth access token using the password grant type.
 * This is a standalone function, primarily for direct testing or specific needs.
 * For general API calls, MPSMonitorClient's internal token management is preferred.
 *
 * @param array $config Configuration array containing TOKEN_URL, CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE.
 * @return string The access token.
 * @throws Exception If token acquisition fails.
 */
function get_token(array $config): string
{
    if (!isset($config['TOKEN_URL'])) {
        custom_log("TOKEN_URL is not defined in .env config.", 'ERROR');
        throw new Exception("TOKEN_URL is not configured.");
    }

    custom_log('Attempting to get token via api_functions.php (standalone).', 'INFO');

    $ch = curl_init();

    $payload = http_build_query([
        'grant_type'    => 'password',
        'username'      => $config['USERNAME'] ?? '',
        'password'      => $config['PASSWORD'] ?? '',
        'client_id'     => $config['CLIENT_ID'] ?? '',
        'client_secret' => $config['CLIENT_SECRET'] ?? '',
        'scope'         => $config['SCOPE'] ?? '',
    ]);

    curl_setopt_array($ch, [
        CURLOPT_URL            => $config['TOKEN_URL'],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
        CURLOPT_FAILONERROR    => false, // We handle errors manually
        CURLOPT_SSL_VERIFYPEER => false, // WARNING: Set to true in production with proper CA certs
        CURLOPT_SSL_VERIFYHOST => false, // WARNING: Set to 2 in production
        CURLOPT_TIMEOUT        => 30, // Timeout after 30 seconds
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        custom_log("cURL error during token fetch in get_token (standalone): " . $error, 'ERROR');
        throw new Exception("Failed to connect to token URL: " . $error);
    }

    $responseData = json_decode($response, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        custom_log("Invalid JSON response from token URL (standalone): " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
        throw new Exception("Invalid JSON response from token endpoint (standalone).");
    }

    // Check HTTP status code and presence of access_token
    if ($httpCode !== 200 || !isset($responseData['access_token'])) {
        custom_log("Token fetch failed in get_token (standalone). HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
        throw new Exception("Failed to get access token (standalone). API Response: " . ($responseData['error_description'] ?? $response));
    }

    custom_log('Token acquired successfully via api_functions.php (standalone).', 'INFO');
    return $responseData['access_token'];
}

/**
 * Makes an authenticated API call to the external service.
 * This is a standalone function. For general API use, prefer MPSMonitorClient.
 *
 * @param array $config Configuration array.
 * @param string $method HTTP method (GET, POST, PUT, DELETE).
 * @param string $path API endpoint path (e.g., 'Customer/GetCustomers').
 * @param array $body Optional request body data for POST/PUT.
 * @return array Decoded JSON response.
 * @throws Exception If API call fails.
 */
function call_api(array $config, string $method, string $path, array $body = []): array
{
    if (!isset($config['API_BASE_URL'])) {
        custom_log("API_BASE_URL is not defined in .env config.", 'ERROR');
        throw new Exception('API_BASE_URL is not configured.');
    }

    try {
        // Use the standalone get_token function here
        $token = get_token($config);
    } catch (Exception $e) {
        custom_log("Authentication failed before API call (standalone): " . $e->getMessage(), 'ERROR');
        throw new Exception('Authentication failed: ' . $e->getMessage());
    }

    $url = rtrim($config['API_BASE_URL'], '/') . '/' . ltrim($path, '/');
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];

    $ch = curl_init();

    if ($method === 'GET' && !empty($body)) {
        $url .= '?' . http_build_query($body);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // WARNING: Set to true in production
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // WARNING: Set to 2 in production
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout after 60 seconds

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $headers[] = 'Content-Type: application/json';
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $headers[] = 'Content-Type: application/json';
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    custom_log('Making API call via api_functions.php (standalone) to: ' . $url . ' (Method: ' . $method . ')', 'DEBUG');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        custom_log("cURL error during API call (standalone) to $url: " . $error, 'ERROR');
        throw new Exception("API request failed: " . $error);
    }

    $responseData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        custom_log("Invalid JSON response from API (standalone) for $url: " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
        throw new Exception("Invalid JSON response from API (standalone). Raw response: " . $response);
    }

    if ($httpCode >= 400) {
        custom_log("API call (standalone) to $url failed. HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
        $errorMessage = $responseData['Message'] ?? $responseData['error_description'] ?? $response;
        throw new Exception("API error (" . $httpCode . "): " . $errorMessage);
    }

    custom_log('API call successful via api_functions.php (standalone) for path: ' . $path . ' (HTTP Code: ' . $httpCode . ')', 'DEBUG');
    return $responseData;
}
?>
