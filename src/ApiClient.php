<?php
// src/ApiClient.php
// -------------------------------------
// API client helper for MPS Monitor OAuth2 token retrieval
// and JSON POST requests. Logs every step to DebugPanel.
// -------------------------------------

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DebugPanel.php';

class ApiClient
{
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;
    private string $scope;
    private string $tokenUrl;
    private string $baseUrl;

    /**
     * Load credentials & URLs from config.php constants.
     */
    public function __construct()
    {
        $this->clientId     = CLIENT_ID;
        $this->clientSecret = CLIENT_SECRET;
        $this->username     = USERNAME;
        $this->password     = PASSWORD;
        $this->scope        = SCOPE;
        $this->tokenUrl     = TOKEN_URL;
        $this->baseUrl      = API_BASE_URL;
    }

    /**
     * Acquire OAuth2 access token via Password Grant.
     *
     * @return string|null Bearer token or null on failure.
     */
    public function getAccessToken(): ?string
    {
        // Build form data
        $form = http_build_query([
            'grant_type'    => 'password',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password,
            'scope'         => $this->scope
        ]);

        // HTTP context
        $opts = ['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => $form,
            'ignore_errors' => true
        ]];

        DebugPanel::log("Requesting token from {$this->tokenUrl}");
        $resp = @file_get_contents($this->tokenUrl, false, stream_context_create($opts));
        if ($resp === false) {
            DebugPanel::log("Token request failed: no response");
            return null;
        }

        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            DebugPanel::log("Token JSON parse error: " . json_last_error_msg());
            return null;
        }

        if (empty($data['access_token'])) {
            $err = $data['error'] ?? 'unknown';
            DebugPanel::log("Token error: $err");
            return null;
        }

        DebugPanel::log("Access token acquired");
        return $data['access_token'];
    }

    /**
     * Send an authorized JSON POST to any endpoint.
     *
     * @param string $path    API path (e.g. '/Device/List')
     * @param string $token   Bearer token
     * @param array  $payload Body as associative array
     * @return array          Decoded JSON response or [] on failure
     */
    public function postJson(string $path, string $token, array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $body = json_encode($payload);
        $headers = [
            "Authorization: Bearer $token",
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        $opts = ['http' => [
            'method'        => 'POST',
            'header'        => implode("\r\n", $headers) . "\r\n",
            'content'       => $body,
            'ignore_errors' => true
        ]];

        DebugPanel::log("POST JSON to $url");
        $resp = @file_get_contents($url, false, stream_context_create($opts));
        if ($resp === false) {
            DebugPanel::log("postJson failed for $url");
            return [];
        }

        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            DebugPanel::log("postJson JSON parse error: " . json_last_error_msg());
            return [];
        }

        DebugPanel::log("postJson success for $url");
        return $data;
    }
}
