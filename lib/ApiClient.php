<?php
class ApiClient {
    private $baseUrl;
    private $credentials;
    private $timeout = 10;

    public function __construct() {
        $this->baseUrl = rtrim(Settings::get('API_BASE_URL'), '/') . '/';
        $this->credentials = [
            'client_id' => Settings::get('CLIENT_ID'),
            'client_secret' => Settings::get('CLIENT_SECRET'),
            'username' => Settings::get('API_USERNAME'),
            'password' => Settings::get('API_PASSWORD'),
            'scope' => Settings::get('API_SCOPE'),
            'grant_type' => 'password'
        ];
    }

    public function getDevices(): array {
        $token = $this->getToken();
        return $this->makeRequest('GET', 'Device/GetDevices', [
            'headers' => ['Authorization: Bearer ' . $token]
        ]);
    }

    private function getToken(): string {
        $token = $_SESSION['api_token'] ?? null;
        if ($token && ($token['expires'] ?? 0) > time() + 60) {
            return $token['value'];
        }
        return $this->refreshToken();
    }

    private function refreshToken(): string {
        $response = $this->makeRequest('POST', 'oauth/token', [
            'form_data' => $this->credentials
        ]);

        if (empty($response['access_token'])) {
            throw new RuntimeException("Failed to obtain access token");
        }

        $_SESSION['api_token'] = [
            'value' => $response['access_token'],
            'expires' => time() + ($response['expires_in'] ?? 3600)
        ];

        return $response['access_token'];
    }

    public function makeRequest(string $method, string $endpoint, array $options = []): array {
        $url = $this->baseUrl . ltrim($endpoint, '/');
        $ch = curl_init();

        $headers = $options['headers'] ?? [];
        $headers[] = 'Accept: application/json';
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        if (!empty($options['form_data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['form_data']));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("cURL error: $error");
        }

        curl_close($ch);
        
        $data = json_decode($response, true) ?? [];
        
        if ($status >= 400) {
            $message = $data['error'] ?? 'API request failed';
            throw new RuntimeException("$message (HTTP $status)");
        }

        return $data;
    }
}