<?php
// public/src/ApiClient.php
// -------------------------------------
// Helper for OAuth2 token (with expiry) & JSON POST.
// -------------------------------------

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DebugPanel.php';

class ApiClient
{
    /**
     * Fetch raw token data from TOKEN_URL.
     * Returns array with access_token, expires_in, etc.
     */
    public function getTokenData(): array
    {
        if (empty(TOKEN_URL)) {
            DebugPanel::log("TOKEN_URL is not set");
            return [];
        }

        $form = http_build_query([
            'grant_type'    => 'password',
            'client_id'     => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'username'      => USERNAME,
            'password'      => PASSWORD,
            'scope'         => SCOPE
        ]);

        DebugPanel::log("Requesting token from " . TOKEN_URL);
        $opts = ['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => $form,
            'ignore_errors' => true
        ]];
        $resp = @file_get_contents(TOKEN_URL, false, stream_context_create($opts));
        if ($resp === false) {
            DebugPanel::log("Failed to fetch token");
            return [];
        }
        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            DebugPanel::log("Token JSON parse error: " . json_last_error_msg());
            return [];
        }
        return $data;
    }

    /**
     * Convenience method - returns just the access token string or null.
     */
    public function getAccessToken(): ?string
    {
        $data = $this->getTokenData();
        return $data['access_token'] ?? null;
    }

    /**
     * POST JSON payload to a given API path using bearer token.
     * Returns decoded array or empty on error.
     */
    public function postJson(string $path, string $token, array $payload): array
    {
        $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($path, '/');
        $body = json_encode($payload);
        $hdrs = [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json"
        ];
        DebugPanel::log("POST $url");
        $opts = ['http' => [
            'method'        => 'POST',
            'header'        => implode("\r\n", $hdrs) . "\r\n",
            'content'       => $body,
            'ignore_errors' => true
        ]];
        $resp = @file_get_contents($url, false, stream_context_create($opts));
        if ($resp === false) {
            DebugPanel::log("POST failed for $url");
            return [];
        }
        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            DebugPanel::log("POST JSON parse error: " . json_last_error_msg());
            return [];
        }
        DebugPanel::log("POST success");
        return $data;
    }
}
