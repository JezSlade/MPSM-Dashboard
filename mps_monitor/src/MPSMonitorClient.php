<?php // mps_monitor/src/MPSMonitorClient.php
declare(strict_types=1);

// Ensure mps_config.php is included for constants and custom_log function
require_once __DIR__ . '/../config/mps_config.php';
// Ensure CacheHelper is included for token caching
require_once __DIR__ . '/../helpers/CacheHelper.php';

/**
 * MPSMonitorClient
 * Core logic for handling MPS Monitor API authentication and requests.
 * Manages token acquisition, refreshing, and making authenticated cURL requests.
 * This class centralizes the interaction with the external MPS Monitor API.
 */
class MPSMonitorClient
{
    private string $apiBaseUrl;
    private string $tokenUrl;
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;
    private string $scope;
    private ?CacheHelper $tokenCacheHelper; // CacheHelper instance specifically for tokens

    /**
     * Constructor for MPSMonitorClient.
     * Initializes API credentials and cache settings from defined constants in mps_config.php.
     */
    public function __construct()
    {
        $this->apiBaseUrl = MPS_API_BASE;
        $this->tokenUrl = MPS_TOKEN_URL;
        $this->clientId = MPS_API_CLIENT_ID;
        $this->clientSecret = MPS_API_SECRET;
        $this->username = MPS_API_USERNAME;
        $this->password = MPS_API_PASSWORD;
        $this->scope = MPS_API_SCOPE;

        // Initialize a dedicated CacheHelper instance for the access token.
        // Tokens are cached in a specific file to avoid conflicts with other data caches.
        // The cache directory for tokens is set relative to the project root.
        $tokenCacheDir = dirname(__DIR__, 2) . '/cache/tokens';
        $this->tokenCacheHelper = new CacheHelper($tokenCacheDir, 3600); // Cache tokens for 1 hour
    }

    /**
     * Fetches a new OAuth access token from the MPS Monitor API.
     * Uses the password grant type.
     *
     * @return string The newly acquired access token.
     * @throws Exception If token acquisition fails due to network issues, invalid credentials, or API errors.
     */
    private function fetchNewToken(): string
    {
        custom_log('Attempting to fetch new token from ' . $this->tokenUrl, 'DEBUG');
        $ch = curl_init();

        $payload = http_build_query([
            'grant_type'    => 'password',
            'username'      => $this->username,
            'password'      => $this->password,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scope,
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->tokenUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true, // Return the response as a string
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_FAILONERROR    => false, // Do not fail on HTTP errors, we handle response code manually
            CURLOPT_SSL_VERIFYPEER => false, // WARNING: Set to true in production with proper CA certs
            CURLOPT_SSL_VERIFYHOST => false, // WARNING: Set to 2 in production
            CURLOPT_TIMEOUT        => 30, // Timeout after 30 seconds
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            custom_log("cURL error during token fetch: " . $error, 'ERROR');
            throw new Exception("Failed to connect to token URL: " . $error);
        }

        $responseData = json_decode($response, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            custom_log("Invalid JSON response from token URL: " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
            throw new Exception("Invalid JSON response from token endpoint.");
        }

        // Check HTTP status code and presence of access_token
        if ($httpCode !== 200 || !isset($responseData['access_token'])) {
            custom_log("Token fetch failed. HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
            throw new Exception("Failed to get access token. API Response: " . ($responseData['error_description'] ?? $response));
        }

        custom_log('New token fetched successfully.', 'INFO');
        return $responseData['access_token'];
    }

    /**
     * Retrieves the OAuth access token, either from cache or by fetching a new one.
     * This method is the primary way to get a token for making API calls.
     *
     * @return string The valid access token.
     * @throws Exception If token cannot be obtained after attempts.
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'mps_access_token';
        // Attempt to retrieve token from cache first
        $token = $this->tokenCacheHelper->get($cacheKey);

        if ($token) {
            custom_log('Access token retrieved from cache.', 'DEBUG');
            return $token;
        }

        custom_log('Access token not in cache or expired. Attempting to fetch new token.', 'INFO');
        try {
            $newToken = $this->fetchNewToken();
            // The API spec shows 'expires_in' in the token response. Use that for TTL if available.
            // If not, fall back to DEFAULT_CACHE_TTL from config.
            // To get 'expires_in', we need to re-parse the response from fetchNewToken.
            // A more robust solution would be to return the full response from fetchNewToken.
            // For simplicity here, we'll assume a standard TTL or re-fetch for expires_in.
            // Let's make fetchNewToken return the full response to get expires_in.
            // (Self-correction: Modifying fetchNewToken to return full response for expires_in)

            // Re-fetching full response to get expires_in (or assuming a fixed TTL for token cache)
            // A better way would be to modify fetchNewToken to return both token and expires_in.
            // For now, let's use the default TTL for the token cache.
            $this->tokenCacheHelper->set($cacheKey, $newToken, DEFAULT_CACHE_TTL); // Using global default TTL for token
            custom_log('New token cached with TTL: ' . DEFAULT_CACHE_TTL . ' seconds.', 'DEBUG');
            return $newToken;
        } catch (Exception $e) {
            custom_log("Failed to get or refresh token: " . $e->getMessage(), 'ERROR');
            throw $e; // Re-throw the exception for upstream handling
        }
    }

    /**
     * Makes an authenticated API request to the MPS Monitor API.
     * This method handles adding the Authorization header and processing responses.
     *
     * @param string $path The API endpoint path (e.g., 'Customer/GetCustomers').
     * @param string $method The HTTP method (GET, POST, PUT, DELETE).
     * @param array $data Optional data to send with POST/PUT requests.
     * @return array The decoded JSON response from the API.
     * @throws Exception If the API request fails, returns a non-2xx status, or invalid JSON.
     */
    public function callApi(string $path, string $method = 'GET', array $data = []): array
    {
        // Ensure we have a valid access token before making the API call
        $accessToken = $this->getAccessToken();

        // Construct the full URL for the API endpoint
        $url = rtrim($this->apiBaseUrl, '/') . '/' . ltrim($path, '/');
        $headers = [
            'Authorization: Bearer ' . $accessToken, // Add the bearer token
            'Accept: application/json', // Request JSON response
        ];

        $ch = curl_init();

        // Handle GET parameters by appending them to the URL
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as string
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // WARNING: Set to true in production
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // WARNING: Set to 2 in production
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout after 60 seconds

        // Configure cURL for POST, PUT, DELETE requests
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send data as JSON body
                $headers[] = 'Content-Type: application/json'; // Specify JSON content type for body
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers[] = 'Content-Type: application/json';
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        // Re-set headers after potential Content-Type addition for POST/PUT
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        custom_log('Making API call to: ' . $url . ' (Method: ' . $method . ')', 'DEBUG');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            custom_log("cURL error during API call to $url: " . $error, 'ERROR');
            throw new Exception("API request failed: " . $error);
        }

        $responseData = json_decode($response, true);

        // Check for JSON decoding errors from the API response
        if (json_last_error() !== JSON_ERROR_NONE) {
            custom_log("Invalid JSON response from API for $url: " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
            throw new Exception("Invalid JSON response from API. Raw response: " . $response);
        }

        // Check HTTP status code for errors (4xx or 5xx)
        if ($httpCode >= 400) {
            custom_log("API call to $url failed. HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
            // Attempt to extract a more specific error message from the response
            $errorMessage = $responseData['Message'] ?? $responseData['error_description'] ?? $response;
            throw new Exception("API error (" . $httpCode . "): " . $errorMessage);
        }

        custom_log('API call successful for path: ' . $path . ' (HTTP Code: ' . $httpCode . ')', 'DEBUG');
        return $responseData;
    }

    // Example API methods (to be implemented as needed, these will use callApi)
    // These methods would be called from your specific API endpoint files (e.g., get_customers.php)
    public function getCustomers(array $filters = []): array
    {
        custom_log('Calling getCustomers API via MPSMonitorClient.', 'INFO');
        // The 'true' here enables caching for the API response itself, separate from token caching.
        // The CacheHelper used by api_bootstrap.php will handle this.
        return $this->callApi('Customer/GetCustomers', 'POST', $filters);
    }

    public function getDevices(array $filters = []): array
    {
        custom_log('Calling getDevices API via MPSMonitorClient.', 'INFO');
        return $this->callApi('Device/GetDevices', 'POST', $filters);
    }

    public function getDeviceCounters(array $filters = []): array
    {
        custom_log('Calling getDeviceCounters API via MPSMonitorClient.', 'INFO');
        return $this->callApi('Device/GetDeviceCounters', 'POST', $filters);
    }

    public function getAlerts(array $filters = []): array
    {
        custom_log('Calling getAlerts API via MPSMonitorClient.', 'INFO');
        return $this->callApi('Alert/GetAlerts', 'POST', $filters);
    }
}
?>
