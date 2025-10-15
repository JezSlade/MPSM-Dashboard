<?php
/**
 * MPS Monitors API Engine
 * 
 * Core engine for MPS Monitors API integration
 * Subdirectory-safe, minimal dependencies, GreenGeeks compatible
 * 
 * @version 1.1.0 - Enhanced error handling and bug fixes
 * @author MPS API Integration Team
 */

class MPSMonitorEngine {
    
    private static $config = null;
    private static $instance = null;
    private static $requestCount = 0;
    
    // Error codes
    const ERR_CONFIG = 1000;
    const ERR_NETWORK = 2000;
    const ERR_API = 3000;
    const ERR_VALIDATION = 4000;
    const ERR_INTERNAL = 5000;
    
    /**
     * Get singleton instance
     * Thread-safe implementation
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize engine with configuration
     * Private constructor for singleton pattern
     */
    private function __construct() {
        if (self::$config === null) {
            self::loadConfig();
        }
    }
    
    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Load configuration from config.php
     */
    private static function loadConfig() {
        $configPath = __DIR__ . '/config.php';
        
        if (!file_exists($configPath)) {
            self::logError('Configuration file not found: ' . $configPath, self::ERR_CONFIG);
            throw new Exception('Configuration file not found', self::ERR_CONFIG);
        }
        
        if (!is_readable($configPath)) {
            self::logError('Configuration file not readable: ' . $configPath, self::ERR_CONFIG);
            throw new Exception('Configuration file not readable', self::ERR_CONFIG);
        }
        
        try {
            self::$config = require $configPath;
        } catch (Exception $e) {
            self::logError('Configuration file load error: ' . $e->getMessage(), self::ERR_CONFIG);
            throw new Exception('Configuration file load failed', self::ERR_CONFIG);
        }
        
        // Validate configuration is array
        if (!is_array(self::$config)) {
            self::logError('Configuration must return array', self::ERR_CONFIG);
            throw new Exception('Invalid configuration format', self::ERR_CONFIG);
        }
        
        // Validate required configuration
        $required = ['MPS_BASE_URL', 'MPS_API_KEY'];
        $missing = [];
        
        foreach ($required as $key) {
            if (empty(self::$config[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            $message = "Missing required configuration: " . implode(', ', $missing);
            self::logError($message, self::ERR_CONFIG);
            throw new Exception($message, self::ERR_CONFIG);
        }
        
        // Validate URL format
        if (!filter_var(self::$config['MPS_BASE_URL'], FILTER_VALIDATE_URL)) {
            self::logError('Invalid MPS_BASE_URL format', self::ERR_CONFIG);
            throw new Exception('Invalid MPS_BASE_URL format', self::ERR_CONFIG);
        }
        
        // Validate API key format (not empty, reasonable length)
        if (strlen(self::$config['MPS_API_KEY']) < 10) {
            self::logError('MPS_API_KEY appears invalid (too short)', self::ERR_CONFIG);
            throw new Exception('MPS_API_KEY appears invalid', self::ERR_CONFIG);
        }
        
        // Set defaults with validation
        self::$config['MPS_TIMEOUT'] = (int)(self::$config['MPS_TIMEOUT'] ?? 30);
        self::$config['MPS_CONNECT_TIMEOUT'] = (int)(self::$config['MPS_CONNECT_TIMEOUT'] ?? 10);
        self::$config['MPS_DEBUG'] = filter_var(self::$config['MPS_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        self::$config['MPS_MAX_RETRIES'] = (int)(self::$config['MPS_MAX_RETRIES'] ?? 3);
        
        // Validate timeout values
        if (self::$config['MPS_TIMEOUT'] < 1 || self::$config['MPS_TIMEOUT'] > 300) {
            self::$config['MPS_TIMEOUT'] = 30;
        }
        
        if (self::$config['MPS_CONNECT_TIMEOUT'] < 1 || self::$config['MPS_CONNECT_TIMEOUT'] > 60) {
            self::$config['MPS_CONNECT_TIMEOUT'] = 10;
        }
    }
    
    /**
     * Make API request to MPS Monitors with retry logic and comprehensive error handling
     * 
     * @param string $endpoint API endpoint (e.g., 'monitors', 'alerts')
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Request payload
     * @param array $queryParams URL query parameters
     * @return array Response data
     */
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = []) {
        // Validate inputs
        if (empty($endpoint) || !is_string($endpoint)) {
            return $this->errorResponse('Invalid endpoint', self::ERR_VALIDATION);
        }
        
        $method = strtoupper(trim($method));
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        if (!in_array($method, $allowedMethods)) {
            return $this->errorResponse("Invalid HTTP method: {$method}", self::ERR_VALIDATION);
        }
        
        // Sanitize endpoint (prevent path traversal)
        $endpoint = str_replace(['..', '\\'], '', $endpoint);
        $endpoint = trim($endpoint, '/');
        
        // Build URL
        $url = rtrim(self::$config['MPS_BASE_URL'], '/') . '/' . $endpoint;
        
        // Add query parameters
        if (!empty($queryParams)) {
            if (!is_array($queryParams)) {
                return $this->errorResponse('Query params must be array', self::ERR_VALIDATION);
            }
            $url .= '?' . http_build_query($queryParams);
        }
        
        // Validate final URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            self::logError("Invalid URL generated: {$url}", self::ERR_VALIDATION);
            return $this->errorResponse('Invalid request URL', self::ERR_VALIDATION);
        }
        
        // Increment request counter
        self::$requestCount++;
        $requestId = uniqid('req_', true);
        
        // Log request in debug mode
        if (self::$config['MPS_DEBUG']) {
            self::logDebug("Request {$requestId}: {$method} {$url}", [
                'data' => $data,
                'params' => $queryParams
            ]);
        }
        
        // Attempt request with retry logic
        $maxRetries = self::$config['MPS_MAX_RETRIES'];
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            $result = $this->executeRequest($url, $method, $data, $requestId, $attempt);
            
            // Success - return immediately
            if ($result['success']) {
                if (self::$config['MPS_DEBUG']) {
                    self::logDebug("Request {$requestId} succeeded on attempt {$attempt}");
                }
                return $result;
            }
            
            // Check if error is retryable
            $lastError = $result;
            if (!$this->isRetryableError($result)) {
                break;
            }
            
            // Exponential backoff before retry
            if ($attempt < $maxRetries) {
                $delay = pow(2, $attempt - 1); // 1, 2, 4 seconds
                self::logError("Request {$requestId} failed on attempt {$attempt}, retrying in {$delay}s", self::ERR_NETWORK);
                sleep($delay);
            }
        }
        
        // All retries exhausted
        self::logError("Request {$requestId} failed after {$attempt} attempts", self::ERR_NETWORK);
        return $lastError;
    }
    
    /**
     * Execute single HTTP request
     * 
     * @param string $url Full request URL
     * @param string $method HTTP method
     * @param array $data Request body
     * @param string $requestId Unique request identifier
     * @param int $attempt Attempt number
     * @return array Response
     */
    private function executeRequest($url, $method, $data, $requestId, $attempt) {
        $ch = curl_init();
        
        if ($ch === false) {
            return $this->errorResponse('Failed to initialize cURL', self::ERR_NETWORK);
        }
        
        // Build headers
        $headers = [
            'Authorization: Bearer ' . self::$config['MPS_API_KEY'],
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MPS-API-Engine/1.1.0',
            'X-Request-ID: ' . $requestId,
            'X-Attempt: ' . $attempt
        ];
        
        // Set common options
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::$config['MPS_TIMEOUT'],
            CURLOPT_CONNECTTIMEOUT => self::$config['MPS_CONNECT_TIMEOUT'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects automatically
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '', // Accept all encodings
            CURLOPT_FAILONERROR => false, // Handle errors manually
        ];
        
        // Set method-specific options
        switch ($method) {
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
            case 'PUT':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
            case 'DELETE':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                if (!empty($data)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'PATCH':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
        }
        
        curl_setopt_array($ch, $curlOptions);
        
        // Execute request
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms
        
        // Get request info
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($curlErrno !== 0) {
            $errorMsg = "cURL Error [{$curlErrno}]: {$curlError}";
            self::logError($errorMsg, self::ERR_NETWORK, [
                'request_id' => $requestId,
                'url' => $url,
                'method' => $method,
                'duration_ms' => $duration
            ]);
            
            return [
                'success' => false,
                'error' => 'Network request failed',
                'error_code' => self::ERR_NETWORK,
                'error_detail' => self::$config['MPS_DEBUG'] ? $errorMsg : null,
                'http_code' => $httpCode,
                'request_id' => $requestId,
                'retryable' => $this->isCurlErrorRetryable($curlErrno)
            ];
        }
        
        // Handle HTTP response
        if ($response === false || $response === '') {
            return $this->errorResponse('Empty response from API', self::ERR_API, [
                'http_code' => $httpCode,
                'request_id' => $requestId
            ]);
        }
        
        // Parse JSON response
        $decoded = json_decode($response, true);
        $jsonError = json_last_error();
        
        if ($jsonError !== JSON_ERROR_NONE) {
            $jsonErrorMsg = json_last_error_msg();
            self::logError("JSON decode error: {$jsonErrorMsg}", self::ERR_API, [
                'request_id' => $requestId,
                'response_preview' => substr($response, 0, 200)
            ]);
            
            // Return raw response if JSON parse fails
            return [
                'success' => false,
                'error' => 'Invalid JSON response from API',
                'error_code' => self::ERR_API,
                'http_code' => $httpCode,
                'raw_response' => self::$config['MPS_DEBUG'] ? $response : null,
                'request_id' => $requestId,
                'retryable' => false
            ];
        }
        
        // Log in debug mode
        if (self::$config['MPS_DEBUG']) {
            self::logDebug("Request {$requestId} completed", [
                'http_code' => $httpCode,
                'duration_ms' => $duration,
                'response_size' => strlen($response)
            ]);
        }
        
        // Success responses (2xx)
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decoded,
                'http_code' => $httpCode,
                'request_id' => $requestId,
                'duration_ms' => $duration
            ];
        }
        
        // Redirect responses (3xx) - treat as errors
        if ($httpCode >= 300 && $httpCode < 400) {
            return $this->errorResponse('Unexpected redirect from API', self::ERR_API, [
                'http_code' => $httpCode,
                'location' => $info['redirect_url'] ?? null,
                'request_id' => $requestId,
                'retryable' => false
            ]);
        }
        
        // Client errors (4xx) - not retryable
        if ($httpCode >= 400 && $httpCode < 500) {
            $errorMsg = $decoded['message'] ?? $decoded['error'] ?? 'Client error';
            self::logError("API Client Error [{$httpCode}]: {$errorMsg}", self::ERR_API, [
                'request_id' => $requestId,
                'url' => $url,
                'method' => $method
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg,
                'error_code' => self::ERR_API,
                'http_code' => $httpCode,
                'raw_response' => $decoded,
                'request_id' => $requestId,
                'retryable' => false
            ];
        }
        
        // Server errors (5xx) - retryable
        if ($httpCode >= 500) {
            $errorMsg = $decoded['message'] ?? $decoded['error'] ?? 'Server error';
            self::logError("API Server Error [{$httpCode}]: {$errorMsg}", self::ERR_API, [
                'request_id' => $requestId,
                'url' => $url,
                'method' => $method
            ]);
            
            return [
                'success' => false,
                'error' => $errorMsg,
                'error_code' => self::ERR_API,
                'http_code' => $httpCode,
                'raw_response' => $decoded,
                'request_id' => $requestId,
                'retryable' => true
            ];
        }
        
        // Unknown status code
        return $this->errorResponse('Unexpected HTTP status code', self::ERR_API, [
            'http_code' => $httpCode,
            'request_id' => $requestId,
            'retryable' => false
        ]);
    }
    
    /**
     * Check if error is retryable
     * 
     * @param array $result Error result
     * @return bool
     */
    private function isRetryableError($result) {
        return isset($result['retryable']) && $result['retryable'] === true;
    }
    
    /**
     * Check if cURL error is retryable
     * 
     * @param int $errno cURL error number
     * @return bool
     */
    private function isCurlErrorRetryable($errno) {
        $retryable = [
            CURLE_COULDNT_CONNECT,      // 7
            CURLE_OPERATION_TIMEDOUT,   // 28
            CURLE_COULDNT_RESOLVE_HOST, // 6
            CURLE_RECV_ERROR,           // 56
            CURLE_SEND_ERROR,           // 55
            CURLE_GOT_NOTHING,          // 52
        ];
        
        return in_array($errno, $retryable);
    }
    
    /**
     * Create standardized error response
     * 
     * @param string $message Error message
     * @param int $errorCode Error code
     * @param array $context Additional context
     * @return array
     */
    private function errorResponse($message, $errorCode, $context = []) {
        $response = [
            'success' => false,
            'error' => $message,
            'error_code' => $errorCode,
            'timestamp' => date('c')
        ];
        
        // Add context in debug mode
        if (self::$config['MPS_DEBUG'] && !empty($context)) {
            $response = array_merge($response, $context);
        }
        
        return $response;
    }
    
    /**
     * Get list of monitors with validation
     */
    public function getMonitors($filters = []) {
        if (!is_array($filters)) {
            return $this->errorResponse('Filters must be an array', self::ERR_VALIDATION);
        }
        return $this->makeRequest('monitors', 'GET', [], $filters);
    }
    
    /**
     * Get specific monitor by ID with validation
     */
    public function getMonitor($monitorId) {
        $monitorId = $this->validateAndSanitizeId($monitorId, 'Monitor');
        if (!$monitorId) {
            return $this->errorResponse('Invalid monitor ID', self::ERR_VALIDATION);
        }
        return $this->makeRequest("monitors/{$monitorId}", 'GET');
    }
    
    /**
     * Create new monitor with validation
     */
    public function createMonitor($monitorData) {
        if (!is_array($monitorData)) {
            return $this->errorResponse('Monitor data must be an array', self::ERR_VALIDATION);
        }
        
        // Validate required fields
        $required = ['name', 'url'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($monitorData[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return $this->errorResponse('Missing required fields: ' . implode(', ', $missing), self::ERR_VALIDATION);
        }
        
        // Validate URL format if present
        if (isset($monitorData['url']) && !filter_var($monitorData['url'], FILTER_VALIDATE_URL)) {
            return $this->errorResponse('Invalid URL format', self::ERR_VALIDATION);
        }
        
        return $this->makeRequest('monitors', 'POST', $monitorData);
    }
    
    /**
     * Update monitor with validation
     */
    public function updateMonitor($monitorId, $monitorData) {
        $monitorId = $this->validateAndSanitizeId($monitorId, 'Monitor');
        if (!$monitorId) {
            return $this->errorResponse('Invalid monitor ID', self::ERR_VALIDATION);
        }
        
        if (!is_array($monitorData)) {
            return $this->errorResponse('Monitor data must be an array', self::ERR_VALIDATION);
        }
        
        if (empty($monitorData)) {
            return $this->errorResponse('No update data provided', self::ERR_VALIDATION);
        }
        
        // Validate URL format if present
        if (isset($monitorData['url']) && !filter_var($monitorData['url'], FILTER_VALIDATE_URL)) {
            return $this->errorResponse('Invalid URL format', self::ERR_VALIDATION);
        }
        
        return $this->makeRequest("monitors/{$monitorId}", 'PUT', $monitorData);
    }
    
    /**
     * Delete monitor with validation
     */
    public function deleteMonitor($monitorId) {
        $monitorId = $this->validateAndSanitizeId($monitorId, 'Monitor');
        if (!$monitorId) {
            return $this->errorResponse('Invalid monitor ID', self::ERR_VALIDATION);
        }
        return $this->makeRequest("monitors/{$monitorId}", 'DELETE');
    }
    
    /**
     * Get alerts with validation
     */
    public function getAlerts($filters = []) {
        if (!is_array($filters)) {
            return $this->errorResponse('Filters must be an array', self::ERR_VALIDATION);
        }
        return $this->makeRequest('alerts', 'GET', [], $filters);
    }
    
    /**
     * Get monitor statistics with validation
     */
    public function getStatistics($monitorId, $period = '24h') {
        $monitorId = $this->validateAndSanitizeId($monitorId, 'Monitor');
        if (!$monitorId) {
            return $this->errorResponse('Invalid monitor ID', self::ERR_VALIDATION);
        }
        
        // Validate period format
        $validPeriods = ['1h', '24h', '7d', '30d', '90d'];
        if (!in_array($period, $validPeriods)) {
            return $this->errorResponse('Invalid period. Valid: ' . implode(', ', $validPeriods), self::ERR_VALIDATION);
        }
        
        return $this->makeRequest("monitors/{$monitorId}/statistics", 'GET', [], ['period' => $period]);
    }
    
    /**
     * Health check with detailed diagnostics
     */
    public function healthCheck() {
        $startTime = microtime(true);
        $diagnostics = [
            'timestamp' => date('c'),
            'engine_version' => '1.1.0',
            'php_version' => PHP_VERSION,
            'request_count' => self::$requestCount,
        ];
        
        try {
            $result = $this->makeRequest('health', 'GET');
            $diagnostics['response_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
            $diagnostics['api_reachable'] = true;
            $diagnostics['api_response'] = $result['success'];
            
            return array_merge($result, $diagnostics);
        } catch (Exception $e) {
            $diagnostics['response_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
            $diagnostics['api_reachable'] = false;
            $diagnostics['error'] = $e->getMessage();
            
            return array_merge(['success' => false], $diagnostics);
        }
    }
    
    /**
     * Get available endpoints
     */
    public function getAvailableEndpoints() {
        return [
            'monitors' => [
                'GET /monitors' => 'List all monitors',
                'GET /monitors/{id}' => 'Get specific monitor',
                'POST /monitors' => 'Create monitor',
                'PUT /monitors/{id}' => 'Update monitor',
                'DELETE /monitors/{id}' => 'Delete monitor'
            ],
            'alerts' => [
                'GET /alerts' => 'List alerts'
            ],
            'statistics' => [
                'GET /monitors/{id}/statistics' => 'Get monitor statistics'
            ],
            'health' => [
                'GET /health' => 'Health check'
            ]
        ];
    }
    
    /**
     * Validate and sanitize ID
     * 
     * @param mixed $id ID to validate
     * @param string $type Type name for error messages
     * @return string|false Sanitized ID or false if invalid
     */
    private function validateAndSanitizeId($id, $type = 'Resource') {
        if (empty($id)) {
            self::logError("{$type} ID is empty", self::ERR_VALIDATION);
            return false;
        }
        
        // Convert to string
        $id = (string)$id;
        
        // Check length
        if (strlen($id) > 255) {
            self::logError("{$type} ID too long", self::ERR_VALIDATION);
            return false;
        }
        
        // Check for path traversal attempts
        if (strpos($id, '..') !== false || strpos($id, '/') !== false || strpos($id, '\\') !== false) {
            self::logError("{$type} ID contains invalid characters", self::ERR_VALIDATION);
            return false;
        }
        
        // Allow alphanumeric, hyphens, underscores only
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            self::logError("{$type} ID contains invalid characters: {$id}", self::ERR_VALIDATION);
            return false;
        }
        
        return $id;
    }
    
    /**
     * Log error to file with context
     * 
     * @param string $message Error message
     * @param int $errorCode Error code
     * @param array $context Additional context
     */
    private static function logError($message, $errorCode = 0, $context = []) {
        $logDir = __DIR__ . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                error_log("Failed to create logs directory: {$logDir}");
                return;
            }
        }
        
        // Verify directory is writable
        if (!is_writable($logDir)) {
            error_log("Logs directory not writable: {$logDir}");
            return;
        }
        
        $logFile = $logDir . '/error_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $errorCodeStr = $errorCode ? " [Code: {$errorCode}]" : '';
        
        // Build log entry
        $logEntry = "[{$timestamp}]{$errorCodeStr} {$message}";
        
        // Add context if present
        if (!empty($context)) {
            $logEntry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        // Add backtrace in debug mode
        if (isset(self::$config['MPS_DEBUG']) && self::$config['MPS_DEBUG']) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $caller = $trace[1] ?? $trace[0];
            $logEntry .= ' | Called from: ' . ($caller['file'] ?? 'unknown') . ':' . ($caller['line'] ?? '?');
        }
        
        $logEntry .= "\n";
        
        // Write to file with file locking
        if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to write to log file: {$logFile}");
        }
    }
    
    /**
     * Log debug information
     * 
     * @param string $message Debug message
     * @param array $context Additional context
     */
    private static function logDebug($message, $context = []) {
        if (!isset(self::$config['MPS_DEBUG']) || !self::$config['MPS_DEBUG']) {
            return;
        }
        
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/debug_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] DEBUG: {$message}";
        
        if (!empty($context)) {
            $logEntry .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        $logEntry .= "\n";
        
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get configuration value
     * 
     * @param string|null $key Configuration key or null for all
     * @return mixed Configuration value or array
     */
    public static function getConfig($key = null) {
        if ($key === null) {
            // Return sanitized config (hide sensitive values)
            $sanitized = self::$config;
            if (isset($sanitized['MPS_API_KEY'])) {
                $sanitized['MPS_API_KEY'] = '***HIDDEN***';
            }
            return $sanitized;
        }
        return self::$config[$key] ?? null;
    }
    
    /**
     * Get engine statistics
     * 
     * @return array
     */
    public static function getStats() {
        return [
            'request_count' => self::$requestCount,
            'uptime' => 'N/A', // Would need process start time tracking
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }
}
