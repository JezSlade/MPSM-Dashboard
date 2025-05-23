<?php
/**
 * core/api.php
 * API Client for MPSM Dashboard
 * Handles authentication and API requests
 */

require_once __DIR__ . '/config.php';    // loads Config::getEnv()
require_once __DIR__ . '/debug.php';     // loads debug_log()
require_once __DIR__ . '/Logger.php';    // ← ensures Logger class is available

/**
 * @reusable
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
    
    /**
     * @reusable
     */
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // Load configuration
        $env = Config::getEnv();
        $this->client_id     = $env['API_CLIENT_ID']     ?? '';
        $this->client_secret = $env['API_CLIENT_SECRET'] ?? '';
        $this->username      = $env['API_USERNAME']      ?? '';
        $this->password      = $env['API_PASSWORD']      ?? '';
        $this->scope         = $env['API_SCOPE']         ?? '';
        $this->token_url     = $env['API_TOKEN_URL']     ?? '';
        $this->base_url      = $env['API_BASE_URL']      ?? '';
        
        // Load endpoints
        $path = __DIR__ . '/../config/endpoints.json';
        $this->api_endpoints = $this->load_endpoints($path);
        
        // Load stored tokens
        $this->load_tokens();
    }
    
    /**
     * @reusable
     */
    private function load_endpoints($json_path) {
        if (!file_exists($json_path)) {
            Logger::error("Endpoints file not found: {$json_path}");
            return [];
        }
        $raw = file_get_contents($json_path);
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error("JSON parse error in endpoints: " . json_last_error_msg());
            return [];
        }
        return $data['paths'] ?? [];
    }
    
    /**
     * @reusable
     */
    private function load_tokens() {
        $stmt = $this->db->prepare(
            "SELECT access_token, refresh_token, token_expiry
               FROM api_tokens
              WHERE id = 1"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->access_token  = $row['access_token'];
            $this->refresh_token = $row['refresh_token'];
            $this->token_expiry  = (int)$row['token_expiry'];
        }
    }
    
    /**
     * @reusable
     */
    private function save_tokens() {
        $stmt = $this->db->prepare("
            INSERT INTO api_tokens
                (id, access_token, refresh_token, token_expiry)
            VALUES
                (1, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                access_token  = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                token_expiry  = VALUES(token_expiry)
        ");
        $stmt->execute([
            $this->access_token,
            $this->refresh_token,
            $this->token_expiry
        ]);
    }
    
    /**
     * @reusable
     */
    private function refreshToken() {
        if (empty($this->refresh_token)) {
            return false;
        }
        $ch = curl_init($this->token_url);
        $post = http_build_query([
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refresh_token
        ]);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/x-www-form-urlencoded",
                "Cache-Control: no-cache"
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200) {
            $data = json_decode($resp, true);
            if (!empty($data['access_token'])) {
                $this->access_token  = $data['access_token'];
                $this->refresh_token = $data['refresh_token'] ?? $this->refresh_token;
                $this->token_expiry  = time() + ($data['expires_in'] ?? 3600);
                $this->save_tokens();
                return true;
            }
        }
        Logger::error("Refresh token failed", ['code'=>$code,'resp'=>$resp]);
        return false;
    }
    
    /**
     * @reusable
     */
    private function obtainToken() {
        $ch = curl_init($this->token_url);
        $post = http_build_query([
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type'    => 'password',
            'username'      => $this->username,
            'password'      => $this->password,
            'scope'         => $this->scope
        ]);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/x-www-form-urlencoded",
                "Cache-Control: no-cache"
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200) {
            $data = json_decode($resp, true);
            if (!empty($data['access_token'])) {
                $this->access_token  = $data['access_token'];
                $this->refresh_token = $data['refresh_token'] ?? null;
                $this->token_expiry  = time() + ($data['expires_in'] ?? 3600);
                $this->save_tokens();
                return true;
            }
        }
        Logger::error("Obtain token failed", ['code'=>$code,'resp'=>$resp]);
        return false;
    }
    
    /**
     * @reusable
     */
    public function getAccessToken() {
        if (empty($this->access_token) || time() >= $this->token_expiry) {
            if (!$this->refreshToken() && !$this->obtainToken()) {
                return null;
            }
        }
        return $this->access_token;
    }
    
    /**
     * @reusable
     */
    public function call_api($endpoint_id, $method='get', $params=[]) {
        if (!isset($this->api_endpoints[$endpoint_id])) {
            Logger::error("Unknown endpoint: $endpoint_id");
            return null;
        }
        $method = strtolower($method);
        if (!isset($this->api_endpoints[$endpoint_id][$method])) {
            Logger::error("Method $method not allowed on $endpoint_id");
            return null;
        }
        $token = $this->getAccessToken();
        if (!$token) {
            Logger::error("No access token");
            return null;
        }
        $url = rtrim($this->base_url,'/') . '/' . ltrim($endpoint_id,'/');
        $ch  = curl_init();
        if ($method === 'get') {
            if ($params) {
                $url .= '?' . http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer $token"
            ]);
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token","Accept: application/json"]
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return json_decode($resp, true);
        }
        Logger::error("API call error on $endpoint_id", ['code'=>$code,'resp'=>$resp]);
        return null;
    }
    
    /**
     * @reusable
     */
    public function get_endpoint_info($endpoint_id) {
        return $this->api_endpoints[$endpoint_id] ?? null;
    }
    
    /**
     * @reusable
     */
    public function get_all_endpoints() {
        return $this->api_endpoints;
    }
}
