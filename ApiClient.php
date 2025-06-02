<?php
class ApiClient {
    private $config;
    private $accessToken;
    private $tokenExpiresAt;

    public function __construct($config) {
        $this->config = $config;
        $this->accessToken = null;
        $this->tokenExpiresAt = 0;
    }

    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        error_log("[$timestamp] $message\n", 3, $this->config['LOG_FILE']);
    }

    private function getAccessToken($retries = 3) {
        if ($this->accessToken && time() < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        $ch = curl_init($this->config['TOKEN_URL']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->config['CLIENT_ID'],
            'client_secret' => $this->config['CLIENT_SECRET'],
            'grant_type' => 'password',
            'username' => $this->config['USERNAME'],
            'password' => $this->config['PASSWORD'],
            'scope' => $this->config['SCOPE'],
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Debug cURL
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log cURL debug info
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        $this->logError("Token request: HTTP $httpCode\n$verboseLog");

        curl_close($ch);
        fclose($verbose);

        if ($httpCode !== 200 || !$response) {
            if ($retries > 0) {
                sleep(1);
                return $this->getAccessToken($retries - 1);
            }
            $this->logError("Token fetch failed: HTTP $httpCode");
            throw new Exception("Failed to obtain access token");
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            $this->logError("Invalid token response: " . print_r($data, true));
            throw new Exception("Invalid token response");
        }

        $this->accessToken = $data['access_token'];
        $this->tokenExpiresAt = time() + ($data['expires_in'] - 60);
        return $this->accessToken;
    }

    public function fetch($endpoint, $method = 'GET', $params = [], $retries = 3) {
        try {
            $token = $this->getAccessToken();
            $url = $this->config['BASE_URL'] . $endpoint;
            if ($method === 'GET' && $params) {
                $url .= '?' . http_build_query($params);
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Cache-Control: no-cache',
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Debug cURL
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);

            if ($method !== 'GET' && $params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Log cURL debug info
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            $this->logError("Fetch $endpoint: HTTP $httpCode\n$verboseLog");
            curl_close($ch);
            fclose($verbose);

            if ($httpCode !== 200) {
                if ($httpCode === 401 && $retries > 0) {
                    $this->accessToken = null; // Force token refresh
                    sleep(1);
                    return $this->fetch($endpoint, $method, $params, $retries - 1);
                }
                $this->logError("Fetch failed for $endpoint: HTTP $httpCode}");
                throw new Exception("HTTP $httpCode: " . ($response ?: 'No response'));
            }

            $data = json_decode($response, true);
            if ($data) {
                $this->logError("Invalid JSON response for $endpoint: " . $response);
                throw new Exception("Invalid JSON response");
            }

            return $data;
        } catch (Exception $e) {
            if ($retries > 0) {
                sleep(1);
                return $this->fetch($endpoint, $method, $params, $retries - 1);
            }
            $this->logError("Fetch error for $endpoint: " . $e->getMessage());
            throw $e;
        }
    }
}