<?php
/**
 * API Client for MPSM Dashboard
 * Handles authentication and API requests
 */
class ApiClient {
    private $client_id;
    private $client_secret;
    private $username;
    private $password;
    private $scope;
    private $token_url;
    private $base_url;
    private $access_token = null;
    private $refresh_token = null;
    private $token_expiry = 0;
    private $api_endpoints;
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // Load configuration from .env file
        $env = Config::getEnv();
        $this->client_id = $env['API_CLIENT_ID'] ?? '';
        $this->client_secret = $env['API_CLIENT_SECRET'] ?? '';
        $this->username = $env['API_USERNAME'] ?? '';
        $this->password = $env['API_PASSWORD'] ?? '';
        $this->scope = $env['API_SCOPE'] ?? '';
        $this->token_url = $env['API_TOKEN_URL'] ?? '';
        $this->base_url = $env['API_BASE_URL'] ?? '';
        
        // Load endpoints from JSON file
        $endpoints_path = 'config/endpoints.json';
        $this->api_endpoints = $this->load_endpoints($endpoints_path);
        
        // Try to load tokens from database
        $this->load_tokens();
    }
    
    private function load_endpoints($json_path) {
        if (!file_exists($json_path)) {
            Logger::error("Endpoints JSON file not found: $json_path");
            return [];
        }
        
        $json_data = file_get_contents($json_path);
        $data = json_decode($json_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error("Error parsing endpoints JSON: " . json_last_error_msg());
            return [];
        }
        
        return $data['paths'] ?? [];
    }
    
    private function load_tokens() {
        $stmt = $this->db->prepare("SELECT access_token, refresh_token, token_expiry FROM api_tokens WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->access_token = $row['access_token'];
            $this->refresh_token = $row['refresh_token'];
            $this->token_expiry = (int)$row['token_expiry'];
        }
    }
    
    private function save_tokens() {
        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (id, access_token, refresh_token, token_expiry) 
            VALUES (1, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            access_token = VALUES(access_token),
            refresh_token = VALUES(refresh_token),
            token_expiry = VALUES(token_expiry)
        ");
        
        $stmt->execute([$this->access_token, $this->refresh_token, $this->token_expiry]);
    }
    
    private function refreshToken() {
        if (empty($this->refresh_token)) {
            return false;
        }
        
        $ch = curl_init($this->token_url);
        
        $payload = http_build_query([
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "grant_type" => "refresh_token",
            "refresh_token" => $this->refresh_token
        ]);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-cache"
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status === 200) {
            $data = json_decode($response, true);
            
            if (isset($data['access_token'])) {
                $this->access_token = $data['access_token'];
                $this->refresh_token = $data['refresh_token'] ?? $this->refresh_token;
                $this->token_expiry = time() + ($data['expires_in'] ?? 3600);
                $this->save_tokens();
                return true;
            }
        }
        
        Logger::error("Token refresh failed. Status: $status, Response: $response");
        return false;
    }
    
    private function obtainToken() {
        $ch = curl_init($this->token_url);
        
        $payload = http_build_query([
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "grant_type" => "password",
            "username" => $this->username,
            "password" => $this->password,
            "scope" => $this->scope
        ]);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-cache"
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status === 200) {
            $data = json_decode($response, true);
            
            if (isset($data['access_token'])) {
                $this->access_token = $data['access_token'];
                $this->refresh_token = $data['refresh_token'] ?? null;
                $this->token_expiry = time() + ($data['expires_in'] ?? 3600);
                $this->save_tokens();
                return true;
            }
        }
        
        Logger::error("Token acquisition failed. Status: $status, Response: $response");
        return false;
    }
    
    public function getAccessToken() {
        if (empty($this->access_token) || time() >= $this->token_expiry) {
            if (!$this->refreshToken()) {
                if (!$this->obtainToken()) {
                    return null;
                }
            }
        }
        
        return $this->access_token;
    }
    
    public function call_api($endpoint_id, $method = 'get', $params = []) {
        // Check if endpoint exists in our API definition
        if (!isset($this->api_endpoints[$endpoint_id])) {
            Logger::error("Endpoint not found in API definition: $endpoint_id");
            return null;
        }
        
        // Check if method is supported for this endpoint
        $method = strtolower($method);
        if (!isset($this->api_endpoints[$endpoint_id][$method])) {
            Logger::error("Method $method not supported for endpoint: $endpoint_id");
            return null;
        }
        
        $token = $this->getAccessToken();
        if (!$token) {
            Logger::error("Failed to obtain access token");
            return null;
        }
        
        $endpoint_url = $this->base_url . $endpoint_id;
        $ch = curl_init();
        
        if ($method === 'get') {
            if (!empty($params)) {
                $query = http_build_query($params);
                $endpoint_url .= '?' . $query;
            }
            
            curl_setopt($ch, CURLOPT_URL, $endpoint_url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_URL, $endpoint_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Authorization: Bearer $token"
                ]);
            }
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Accept: application/json"
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status >= 200 && $status < 300) {
            return json_decode($response, true);
        } else {
            Logger::error("API call failed. Endpoint: $endpoint_id, Status: $status, Response: $response");
            return null;
        }
    }
    
    public function get_endpoint_info($endpoint_id) {
        return $this->api_endpoints[$endpoint_id] ?? null;
    }
    
    public function get_all_endpoints() {
        return $this->api_endpoints;
    }
}
