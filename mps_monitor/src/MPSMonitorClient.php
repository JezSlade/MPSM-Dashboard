<?php // mps_monitor/src/MPSMonitorClient.php
// ORIGINAL FILE FULLY RESTORED — ONLY Swagger COMPLIANCE CHANGE IS IN getCustomers()

declare(strict_types=1);

require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';

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

    public function __construct()
    {
        $this->apiBaseUrl = MPS_API_BASE;
        $this->tokenUrl = MPS_TOKEN_URL;
        $this->clientId = MPS_API_CLIENT_ID;
        $this->clientSecret = MPS_API_SECRET;
        $this->username = MPS_API_USERNAME;
        $this->password = MPS_API_PASSWORD;
        $this->scope = MPS_API_SCOPE;

        $tokenCacheDir = dirname(__DIR__, 2) . '/cache/tokens';
        $this->tokenCacheHelper = new CacheHelper($tokenCacheDir, 3600);
    }

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
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            custom_log("Invalid JSON response from token URL: " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
            throw new Exception("Invalid JSON response from token endpoint.");
        }

        if ($httpCode !== 200 || !isset($responseData['access_token'])) {
            custom_log("Token fetch failed. HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
            throw new Exception("Failed to get access token. API Response: " . ($responseData['error_description'] ?? $response));
        }

        custom_log('New token fetched successfully.', 'INFO');
        return $responseData['access_token'];
    }

    public function getAccessToken(): string
    {
        $cacheKey = 'mps_access_token';
        $token = $this->tokenCacheHelper->get($cacheKey);

        if ($token) {
            custom_log('Access token retrieved from cache.', 'DEBUG');
            return $token;
        }

        custom_log('Access token not in cache or expired. Attempting to fetch new token.', 'INFO');
        $newToken = $this->fetchNewToken();
        $this->tokenCacheHelper->set($cacheKey, $newToken, DEFAULT_CACHE_TTL);
        custom_log('New token cached with TTL: ' . DEFAULT_CACHE_TTL . ' seconds.', 'DEBUG');
        return $newToken;
    }

    public function callApi(string $path, string $method = 'GET', array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        $url = rtrim($this->apiBaseUrl, '/') . '/' . ltrim($path, '/');
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ];

        $ch = curl_init();

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers[] = 'Content-Type: application/json';
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            custom_log("Invalid JSON response from API for $url: " . json_last_error_msg() . ", Raw: " . $response, 'ERROR');
            throw new Exception("Invalid JSON response from API. Raw response: " . $response);
        }

        if ($httpCode >= 400) {
            custom_log("API call to $url failed. HTTP Code: " . $httpCode . ", Response: " . $response, 'ERROR');
            $errorMessage = $responseData['Message'] ?? $responseData['error_description'] ?? $response;
            throw new Exception("API error (" . $httpCode . "): " . $errorMessage);
        }

        custom_log('API call successful for path: ' . $path . ' (HTTP Code: ' . $httpCode . ')', 'DEBUG');
        return $responseData;
    }

    // ✅ ONLY Swagger COMPLIANCE CHANGE
    public function getCustomers(array $filters = []): array
    {
        custom_log('Calling getCustomers API via MPSMonitorClient (GET method).', 'INFO');
        return $this->callApi('Customer/GetCustomers', 'GET', $filters);
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
