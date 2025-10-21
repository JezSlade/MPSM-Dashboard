<?php
require_once __DIR__ . '/SwaggerActionRegistry.php';
require_once __DIR__ . '/DomainSeeder.php';
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
    private static $authMode = 'api_key';
    private static $accessToken = null;
    private static $accessTokenExpiresAt = 0;
    private static $lastAuthError = null;
    private static $actionRegistry = null;
    private static $payloadTemplates = null;
    private static $domainSeeds = null;
    private static $seedsCollected = false;

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

        if (self::$actionRegistry === null) {
            self::$actionRegistry = SwaggerActionRegistry::getInstance();
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
        if (empty(self::$config['MPS_BASE_URL']) || !filter_var(self::$config['MPS_BASE_URL'], FILTER_VALIDATE_URL)) {
            self::logError('Invalid or missing MPS_BASE_URL', self::ERR_CONFIG);
            throw new Exception('Invalid MPS_BASE_URL format', self::ERR_CONFIG);
        }

        self::$authMode = self::$config['AUTH_MODE'] ?? 'api_key';

        if (self::$authMode === 'api_key') {
            if (empty(self::$config['MPS_API_KEY']) || strlen(self::$config['MPS_API_KEY']) < 10) {
                self::logError('MPS_API_KEY appears invalid or missing', self::ERR_CONFIG);
                throw new Exception('MPS_API_KEY appears invalid', self::ERR_CONFIG);
            }
        } elseif (self::$authMode === 'oauth_password') {
            $oauthRequired = ['TOKEN_URL', 'CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE'];
            $missingOauth = [];
            foreach ($oauthRequired as $key) {
                if (empty(self::$config[$key])) {
                    $missingOauth[] = $key;
                }
            }
            if (!empty($missingOauth)) {
                $message = 'Missing OAuth configuration values: ' . implode(', ', $missingOauth);
                self::logError($message, self::ERR_CONFIG);
                throw new Exception($message, self::ERR_CONFIG);
            }
        } else {
            self::logError('Unknown AUTH_MODE: ' . self::$authMode, self::ERR_CONFIG);
            throw new Exception('Unsupported authentication mode: ' . self::$authMode, self::ERR_CONFIG);
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
     * Load payload templates from discovered API patterns
     * Templates provide working parameter defaults from API discovery phase
     */
    private static function loadPayloadTemplates() {
        $templatePath = __DIR__ . '/payload_templates.php';

        if (!file_exists($templatePath)) {
            self::logDebug('Payload templates file not found - using basic defaults only');
            self::$payloadTemplates = [];
            return;
        }

        try {
            // Define constant to allow template file to load
            if (!defined('MPS_ENGINE_ACCESS')) {
                define('MPS_ENGINE_ACCESS', true);
            }

            $templates = require $templatePath;

            if (!is_array($templates)) {
                self::logWarning('Payload templates must return array - ignoring');
                self::$payloadTemplates = [];
                return;
            }

            self::$payloadTemplates = $templates;
            self::logDebug('Loaded ' . count($templates) . ' payload templates');

        } catch (Exception $e) {
            self::logWarning('Failed to load payload templates: ' . $e->getMessage());
            self::$payloadTemplates = [];
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
    public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = [], array $options = []) {
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

        $contentType = $options['contentType'] ?? null;
        $additionalHeaders = $options['headers'] ?? [];
        $rawBody = $options['rawBody'] ?? false;
        $forceBody = $options['forceBody'] ?? false;
        
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
            
            $authCheck = $this->prepareAuthorization();
            if ($authCheck !== true) {
                return $authCheck;
            }
            
            $result = $this->executeRequest($url, $method, $data, $requestId, $attempt, [
                'contentType' => $contentType,
                'headers' => $additionalHeaders,
                'rawBody' => $rawBody,
                'forceBody' => $forceBody,
            ]);
            
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
     * Dispatch an action defined in the swagger specification.
     *
     * @param string $action
     * @param array $params
     * @return array
     */
    public function dispatchAction(string $action, array $params = [])
    {
        if (self::$actionRegistry === null) {
            self::$actionRegistry = SwaggerActionRegistry::getInstance();
        }

        if (empty($action)) {
            return $this->errorResponse('Action name is required.', self::ERR_VALIDATION);
        }

        $operation = self::$actionRegistry->getOperation($action);
        if ($operation === null) {
            return $this->errorResponse("Unknown action: {$action}", self::ERR_VALIDATION);
        }

        if (!is_array($params)) {
            return $this->errorResponse('Params must be an object.', self::ERR_VALIDATION);
        }

        // Load payload templates if not already loaded
        if (self::$payloadTemplates === null) {
            self::loadPayloadTemplates();
        }

        // Initialize domain seeds on first request (lazy loading)
        // Skip seed collection for seed-collecting actions to avoid recursion
        $skipSeedInit = in_array($action, [
            'Integrations/GetJoinedCustomers',
            'ApiClient/List',
            'Role/List',
            'CustomField/List',
            'Product/GetBrands',
            'Product/GetModels'
        ]);

        if (!self::$seedsCollected && !$skipSeedInit) {
            $this->initializeDomainSeeds();
        }

        // Merge discovered payload template if available
        if (isset(self::$payloadTemplates[$action])) {
            $template = self::$payloadTemplates[$action];

            if (self::$config['MPS_DEBUG']) {
                self::logDebug("Using payload template for action: {$action}");
            }

            // Merge template parameters with user-provided params
            // User params always override template values
            foreach (['query', 'path', 'body'] as $paramType) {
                if (isset($template[$paramType]) && is_array($template[$paramType])) {
                    foreach ($template[$paramType] as $key => $templateValue) {
                        // Only use template value if user didn't provide one
                        if (!array_key_exists($key, $params)) {
                            // Apply smart substitution for template values
                            $substitutedValue = $this->substituteTemplateValue($key, $templateValue);
                            if ($substitutedValue !== null) {
                                $params[$key] = $substitutedValue;

                                if (self::$config['MPS_DEBUG']) {
                                    self::logDebug("Template: Adding '{$key}' = " . json_encode($substitutedValue));
                                }
                            }
                        }
                    }
                }
            }
        }

        $endpoint = ltrim($operation['path'], '/');
        $remaining = $params;
        $query = [];
        $headers = [];
        $body = [];

        // Resolve path parameters
        foreach ($operation['pathParams'] as $name => $meta) {
            if (!array_key_exists($name, $remaining)) {
                // Try to get default value for path parameters too
                $defaultValue = $this->getDefaultParameterValue($name, $meta['type'] ?? 'string');

                if ($defaultValue !== null) {
                    $value = $defaultValue;
                    if (self::$config['MPS_DEBUG']) {
                        self::logDebug("Auto-populated required path parameter '{$name}' with default value");
                    }
                } else {
                    return $this->errorResponse("Missing required path parameter: {$name}", self::ERR_VALIDATION);
                }
            } else {
                $value = $remaining[$name];
                unset($remaining[$name]);
            }
            $endpoint = preg_replace('/{' . preg_quote($name, '/') . '}/', rawurlencode((string) $value), $endpoint);
        }

        if (strpos($endpoint, '{') !== false) {
            return $this->errorResponse('Missing path parameter values for action ' . $operation['action'], self::ERR_VALIDATION);
        }

        // Resolve query parameters
        foreach ($operation['queryParams'] as $name => $meta) {
            if (array_key_exists($name, $remaining)) {
                $query[$name] = $remaining[$name];
                unset($remaining[$name]);
            } elseif (!empty($meta['required'])) {
                // Try to get default value before erroring
                $defaultValue = $this->getDefaultParameterValue($name, $meta['type'] ?? 'string');

                if ($defaultValue !== null) {
                    $query[$name] = $defaultValue;
                    // Log that we auto-populated (only in debug mode)
                    if (self::$config['MPS_DEBUG']) {
                        self::logDebug("Auto-populated required parameter '{$name}' with default value");
                    }
                } else {
                    return $this->errorResponse("Missing required query parameter: {$name}", self::ERR_VALIDATION);
                }
            }
        }

        // Resolve header parameters
        foreach ($operation['headerParams'] as $name => $meta) {
            if (array_key_exists($name, $remaining)) {
                $headers[$name] = $remaining[$name];
                unset($remaining[$name]);
            } elseif (!empty($meta['required'])) {
                return $this->errorResponse("Missing required header parameter: {$name}", self::ERR_VALIDATION);
            }
        }

        $contentType = $this->determineContentType($operation['consumes'] ?? []);

        if ($operation['hasBody']) {
            if (!empty($operation['formParams'])) {
                $formData = [];
                foreach ($operation['formParams'] as $name => $meta) {
                    if (array_key_exists($name, $remaining)) {
                        $formData[$name] = $remaining[$name];
                        unset($remaining[$name]);
                    } elseif (!empty($meta['required'])) {
                        return $this->errorResponse("Missing required form parameter: {$name}", self::ERR_VALIDATION);
                    }
                }
                $body = $formData;
                if ($contentType === null || stripos($contentType, 'json') !== false) {
                    $contentType = 'application/x-www-form-urlencoded';
                }
            } else {
                if (!empty($operation['bodyParam']) && array_key_exists($operation['bodyParam'], $params)) {
                    $body = $params[$operation['bodyParam']];
                    unset($remaining[$operation['bodyParam']]);
                } else {
                    $body = $remaining;
                }
            }
        }

        if ($operation['hasBody'] === false && !empty($remaining)) {
            $query = array_merge($query, $remaining);
            $remaining = [];
        }

        if ($operation['hasBody'] && empty($body) && !empty($remaining)) {
            $body = $remaining;
            $remaining = [];
        }

        if (!is_array($body) && !is_string($body) && !is_object($body)) {
            $body = (array) $body;
        }

        if ($operation['hasBody'] === false) {
            $body = [];
        } elseif (is_array($body) && empty($body) && empty($operation['formParams'])) {
            $body = new stdClass();
        }

        $options = [
            'headers' => $headers,
            'contentType' => $contentType,
        ];

        return $this->makeRequest($endpoint, $operation['method'], $body, $query, $options);
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
    private function executeRequest($url, $method, $data, $requestId, $attempt, array $options = []) {
        $ch = curl_init();

        if ($ch === false) {
            return $this->errorResponse('Failed to initialize cURL', self::ERR_NETWORK);
        }

        $contentType = $options['contentType'] ?? null;
        $rawBody = $options['rawBody'] ?? false;
        $forceBody = $options['forceBody'] ?? false;
        $additionalHeaders = $options['headers'] ?? [];

        $sendBody = $forceBody;
        if (!$sendBody) {
            if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
                $sendBody = true;
            } elseif ($method === 'DELETE' && !empty($data)) {
                $sendBody = true;
            } elseif (!empty($data) && is_string($data)) {
                $sendBody = true;
            }
        }

        if ($sendBody && $contentType === null && !$rawBody) {
            $contentType = 'application/json';
        }

        // Normalize additional headers
        $headersAssoc = [];
        $headersAssoc['Accept'] = 'application/json';
        if ($contentType) {
            $headersAssoc['Content-Type'] = $contentType;
        }
        $headersAssoc['User-Agent'] = 'MPS-API-Engine/1.1.0';
        $headersAssoc['X-Request-ID'] = $requestId;
        $headersAssoc['X-Attempt'] = $attempt;

        if (is_array($additionalHeaders)) {
            foreach ($additionalHeaders as $key => $value) {
                if (is_int($key)) {
                    $parts = explode(':', $value, 2);
                    if (count($parts) === 2) {
                        $headersAssoc[trim($parts[0])] = trim($parts[1]);
                    }
                } else {
                    $headersAssoc[$key] = $value;
                }
            }
        }

        if (!isset($headersAssoc['Authorization'])) {
            $headersAssoc['Authorization'] = $this->getAuthorizationHeader();
        }

        $headers = [];
        foreach ($headersAssoc as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        // Set common options
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::$config['MPS_TIMEOUT'],
            CURLOPT_CONNECTTIMEOUT => self::$config['MPS_CONNECT_TIMEOUT'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_FAILONERROR => false,
        ];

        switch ($method) {
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        if ($sendBody) {
            $payload = null;
            if ($rawBody && is_string($data)) {
                $payload = $data;
            } else {
                if ($contentType === 'application/x-www-form-urlencoded') {
                    $payload = is_array($data) ? http_build_query($data) : (string) $data;
                } else {
                    $payload = json_encode($data);
                }
            }

            if ($payload !== null) {
                $curlOptions[CURLOPT_POSTFIELDS] = $payload;
            }
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

        if ($httpCode === 401) {
            if (self::$authMode === 'oauth_password') {
                $this->clearAccessToken();
            }
            $errorMsg = $decoded['message'] ?? $decoded['error'] ?? 'Unauthorized';
            return [
                'success' => false,
                'error' => $errorMsg,
                'error_code' => self::ERR_API,
                'http_code' => $httpCode,
                'raw_response' => self::$config['MPS_DEBUG'] ? $decoded : null,
                'request_id' => $requestId,
                'retryable' => self::$authMode === 'oauth_password'
            ];
        }
        
        // Success responses (2xx)
        if ($httpCode >= 200 && $httpCode < 300) {
            // Validate MPSM-specific response format
            $validation = $this->validateMPSMResponse($decoded, $httpCode);

            if (!$validation['valid']) {
                // MPSM returned 200 but indicated an error via IsValid field
                self::logError("MPSM API Error: {$validation['error']}", self::ERR_API, [
                    'request_id' => $requestId,
                    'url' => $url,
                    'method' => $method,
                    'error_details' => $validation['details']
                ]);

                return [
                    'success' => false,
                    'error' => $validation['error'],
                    'error_code' => self::ERR_API,
                    'http_code' => $httpCode,
                    'raw_response' => self::$config['MPS_DEBUG'] ? $validation['details'] : null,
                    'request_id' => $requestId,
                    'retryable' => false
                ];
            }

            // Valid response - return the validated data
            return [
                'success' => true,
                'data' => $validation['data'],
                'http_code' => $httpCode,
                'request_id' => $requestId,
                'duration_ms' => $duration
            ];
        }
        
        // Redirect responses (3xx) - treat as errors
        if ($httpCode >= 300 && $httpCode < 400) {
            return $this->errorResponse('Unexpected redirect from API', self::ERR_API, [
                'http_code' => $httpCode,
                'location' => null,
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

    private function determineContentType(array $consumes): ?string
    {
        if (empty($consumes)) {
            return null;
        }

        foreach ($consumes as $type) {
            if (stripos($type, 'json') !== false) {
                return 'application/json';
            }
        }

        foreach ($consumes as $type) {
            if (stripos($type, 'form') !== false || stripos($type, 'urlencoded') !== false) {
                return 'application/x-www-form-urlencoded';
            }
        }

        return $consumes[0];
    }

    /**
     * Ensure authentication is ready before dispatching a request.
     *
     * @return true|array true on success or error response array
     */
    private function prepareAuthorization() {
        if (self::$authMode === 'api_key') {
            return true;
        }

        $token = $this->ensureAccessToken();
        if ($token === null) {
            self::logError('Failed to obtain OAuth access token', self::ERR_INTERNAL, [
                'error_detail' => self::$lastAuthError
            ]);
            return $this->errorResponse('Failed to obtain access token', self::ERR_INTERNAL, [
                'auth_error' => self::$lastAuthError
            ]);
        }

        return true;
    }

    /**
     * Build the Authorization header value.
     */
    private function getAuthorizationHeader(): string {
        if (self::$authMode === 'api_key') {
            return 'Bearer ' . self::$config['MPS_API_KEY'];
        }

        return 'Bearer ' . (self::$accessToken ?? '');
    }

    /**
     * Ensure a valid access token is loaded in memory.
     */
    private function ensureAccessToken(): ?string {
        if (self::$accessToken && (self::$accessTokenExpiresAt - 60) > time()) {
            return self::$accessToken;
        }

        return $this->fetchAccessToken();
    }

    /**
     * Request a fresh access token using password grant.
     */
    private function fetchAccessToken(): ?string {
        self::$lastAuthError = null;

        $payload = http_build_query([
            'grant_type' => 'password',
            'client_id' => self::$config['CLIENT_ID'],
            'client_secret' => self::$config['CLIENT_SECRET'],
            'username' => self::$config['USERNAME'],
            'password' => self::$config['PASSWORD'],
            'scope' => self::$config['SCOPE']
        ]);

        $ch = curl_init(self::$config['TOKEN_URL']);
        if ($ch === false) {
            self::$lastAuthError = 'Failed to initialize token request';
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => self::$config['MPS_TIMEOUT'],
            CURLOPT_CONNECTTIMEOUT => self::$config['MPS_CONNECT_TIMEOUT'],
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlErrno !== 0) {
            self::$lastAuthError = $curlError ?: 'Token request failed';
            return null;
        }

        $decoded = json_decode($response, true);
        if ($httpCode < 200 || $httpCode >= 300) {
            self::$lastAuthError = $decoded['error_description'] ?? $decoded['error'] ?? ('Token request failed (HTTP ' . $httpCode . ')');
            return null;
        }

        if (!is_array($decoded) || empty($decoded['access_token'])) {
            self::$lastAuthError = $decoded['error_description'] ?? $decoded['error'] ?? 'Invalid token response';
            return null;
        }

        $expiresIn = (int)($decoded['expires_in'] ?? 3600);
        if ($expiresIn < 60) {
            $expiresIn = 60;
        }

        self::$accessToken = $decoded['access_token'];
        self::$accessTokenExpiresAt = time() + $expiresIn;
        self::$lastAuthError = null;

        return self::$accessToken;
    }

    /**
     * Clear cached access token forcing renewal on next request.
     */
    private function clearAccessToken(): void {
        self::$accessToken = null;
        self::$accessTokenExpiresAt = 0;
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
     * Validate MPSM API response format.
     * MPSM API returns HTTP 200 even for errors - must check IsValid field.
     *
     * @param mixed $responseData Parsed response data
     * @param int $httpStatus HTTP status code
     * @return array ['valid' => bool, 'data' => mixed, 'error' => string|null, 'details' => array|null]
     */
    private function validateMPSMResponse($responseData, $httpStatus) {
        // If not 200, it's a real HTTP error
        if ($httpStatus !== 200) {
            return [
                'valid' => false,
                'error' => "HTTP {$httpStatus} error",
                'details' => $responseData,
                'data' => null
            ];
        }

        // Check for MPSM-specific response structure
        if (!is_array($responseData)) {
            // Might be raw data (non-MPSM endpoint)
            return [
                'valid' => true,
                'data' => $responseData,
                'error' => null,
                'details' => null
            ];
        }

        // Check for IsValid field (MPSM standard response format)
        if (isset($responseData['IsValid'])) {
            if ($responseData['IsValid'] === false) {
                // Extract errors from Errors array
                $errors = $responseData['Errors'] ?? [];
                $errorMessages = [];

                foreach ($errors as $error) {
                    if (isset($error['Description'])) {
                        $errorMessages[] = $error['Description'];
                    } elseif (isset($error['Code'])) {
                        $errorMessages[] = $error['Code'];
                    }
                }

                $errorString = !empty($errorMessages)
                    ? implode('; ', $errorMessages)
                    : 'Request failed';

                return [
                    'valid' => false,
                    'error' => $errorString,
                    'details' => $errors,
                    'data' => null
                ];
            }

            // Valid response - extract Result field if present
            return [
                'valid' => true,
                'data' => $responseData['Result'] ?? $responseData,
                'error' => null,
                'details' => null
            ];
        }

        // No IsValid field - assume valid and return as-is
        return [
            'valid' => true,
            'data' => $responseData,
            'error' => null,
            'details' => null
        ];
    }

    /**
     * Get default value for a parameter based on its name and context.
     * Auto-populates dealer codes, customer codes, and pagination defaults.
     *
     * @param string $paramName Parameter name
     * @param string $paramType Parameter type (string, integer, etc.)
     * @return mixed|null Default value or null if no default available
     */
    /**
     * Apply smart substitutions to template values
     * Converts null placeholders to actual config values or sensible defaults
     *
     * @param string $paramName Parameter name
     * @param mixed $templateValue Value from template (often null)
     * @return mixed Substituted value or original if no substitution needed
     */
    private function substituteTemplateValue($paramName, $templateValue) {
        // If template already has a concrete value, use it as-is
        if ($templateValue !== null) {
            return $templateValue;
        }

        // For null template values, try to substitute with intelligent defaults
        $paramLower = strtolower($paramName);

        // Dealer information - HARD-CODED DEFAULTS (NY06AGDWUQ / SZ13qRwU5GtFLj0i_CbEgQ2)
        // This is the only dealer code we will ever use
        if ($paramLower === 'code' || $paramLower === 'dealercode' || $paramLower === 'dealer_code') {
            return self::$config['DEALER_CODE'] ?? 'NY06AGDWUQ';
        }

        if ($paramLower === 'dealerid' || $paramLower === 'dealer_id') {
            return self::$config['DEALER_ID'] ?? 'SZ13qRwU5GtFLj0i_CbEgQ2';
        }

        // Customer information - try domain seeds first
        if ($paramLower === 'customercode' || $paramLower === 'customer_code') {
            // Try domain seeds first
            if (self::$domainSeeds !== null) {
                $seed = DomainSeeder::getSeedFor('customerCode');
                if ($seed !== null) {
                    return $seed;
                }
            }
            return self::$config['CUSTOMER_CODE'] ?? null;
        }

        if ($paramLower === 'customerid' || $paramLower === 'customer_id') {
            // Try domain seeds first
            if (self::$domainSeeds !== null) {
                $seed = DomainSeeder::getSeedFor('customerId');
                if ($seed !== null) {
                    return $seed;
                }
            }
            return self::$config['CUSTOMER_ID'] ?? null;
        }

        // Device ID - try domain seeds
        if ($paramLower === 'id' || $paramLower === 'deviceid' || $paramLower === 'device_id') {
            if (self::$domainSeeds !== null) {
                $seed = DomainSeeder::getSeedFor('id');
                if ($seed !== null) {
                    return $seed;
                }
            }
            return null;
        }

        // Date parameters - use reasonable date ranges
        if (strpos($paramLower, 'fromdate') !== false || strpos($paramLower, 'startdate') !== false) {
            // Default to 30 days ago
            return date('Y-m-d', strtotime('-30 days'));
        }

        if (strpos($paramLower, 'todate') !== false || strpos($paramLower, 'enddate') !== false) {
            // Default to today
            return date('Y-m-d');
        }

        // Pagination defaults
        if ($paramLower === 'page' || $paramLower === 'pagenumber') {
            return 1;
        }

        if ($paramLower === 'pagesize' || $paramLower === 'pagerows' || $paramLower === 'limit' || $paramLower === 'per_page') {
            return 50;
        }

        // Sort defaults
        if ($paramLower === 'sortorder') {
            return 'Asc';
        }

        if ($paramLower === 'sortcolumn') {
            return 'Name'; // Safe default for most list endpoints
        }

        // Industry standard repair (mentioned by user)
        if ($paramLower === 'repairtype' || $paramLower === 'repair_type') {
            return 'Standard';
        }

        // Keep null if no substitution rule found
        // This allows required parameter validation to trigger if truly needed
        return null;
    }

    private function getDefaultParameterValue($paramName, $paramType = 'string') {
        $paramLower = strtolower($paramName);

        // Auto-populate dealer information
        if ($paramLower === 'code' || $paramLower === 'dealercode' || $paramLower === 'dealer_code') {
            return self::$config['DEALER_CODE'] ?? null;
        }

        if ($paramLower === 'dealerid' || $paramLower === 'dealer_id') {
            return self::$config['DEALER_ID'] ?? null;
        }

        // Auto-populate customer information (if available in config)
        if ($paramLower === 'customercode' || $paramLower === 'customer_code') {
            return self::$config['CUSTOMER_CODE'] ?? null;
        }

        if ($paramLower === 'customerid' || $paramLower === 'customer_id') {
            return self::$config['CUSTOMER_ID'] ?? null;
        }

        // Pagination defaults
        if ($paramLower === 'page' || $paramLower === 'pagenumber') {
            return 1;
        }

        if ($paramLower === 'pagesize' || $paramLower === 'limit' || $paramLower === 'per_page') {
            return 50;
        }

        // No default available
        return null;
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
     * Initialize domain seeds by calling prerequisite endpoints
     */
    private function initializeDomainSeeds() {
        if (self::$seedsCollected) {
            return; // Already collected
        }

        try {
            DomainSeeder::init($this);
            $result = DomainSeeder::collectSeeds();

            if ($result['success']) {
                self::$domainSeeds = DomainSeeder::getSeeds();
                self::$seedsCollected = true;

                if (self::$config['MPS_DEBUG']) {
                    self::logDebug('Domain seeds collected: ' . json_encode(self::$domainSeeds));
                }
            }
        } catch (Exception $e) {
            self::logWarning('Failed to collect domain seeds: ' . $e->getMessage());
            self::$domainSeeds = [];
            self::$seedsCollected = true; // Don't keep retrying
        }
    }

    /**
     * Get domain seeds (for diagnostics/testing)
     */
    public function getDomainSeeds() {
        if (!self::$seedsCollected) {
            $this->initializeDomainSeeds();
        }
        return self::$domainSeeds ?? [];
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
            // Use a real working endpoint to test connectivity
            // AlertLimit/Dealer/Get is a simple GET endpoint that works with just dealer code
            $result = $this->dispatchAction('AlertLimit/Dealer/Get', []);
            $diagnostics['response_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
            $diagnostics['api_reachable'] = true;
            $diagnostics['api_response'] = $result['success'];
            $diagnostics['test_endpoint'] = 'AlertLimit/Dealer/Get';

            // Return simplified health status (not full dealer data)
            return [
                'success' => $result['success'],
                'status' => $result['success'] ? 'healthy' : 'degraded',
                'message' => $result['success'] ? 'API connection successful' : 'API returned error',
                'timestamp' => $diagnostics['timestamp'],
                'engine_version' => $diagnostics['engine_version'],
                'php_version' => $diagnostics['php_version'],
                'response_time' => $diagnostics['response_time'],
                'api_reachable' => $diagnostics['api_reachable'],
                'api_response' => $diagnostics['api_response'],
                'test_endpoint' => $diagnostics['test_endpoint'],
            ];
        } catch (Exception $e) {
            $diagnostics['response_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
            $diagnostics['api_reachable'] = false;
            $diagnostics['error'] = $e->getMessage();

            return array_merge([
                'success' => false,
                'status' => 'unhealthy',
                'message' => 'Health check failed'
            ], $diagnostics);
        }
    }
    
    /**
     * Get available endpoints
     */
    public function getAvailableEndpoints() {
        if (self::$actionRegistry === null) {
            self::$actionRegistry = SwaggerActionRegistry::getInstance();
        }

        $operations = self::$actionRegistry->listOperations();
        $grouped = [];

        foreach ($operations as $operation) {
            $category = $operation['action'];
            if (strpos($category, '/') !== false) {
                $category = substr($category, 0, strpos($category, '/'));
            } else {
                $category = 'general';
            }

            $category = strtolower($category);

            $grouped[$category][] = [
                'action' => $operation['action'],
                'method' => $operation['method'],
                'path' => '/' . ltrim($operation['path'], '/'),
                'summary' => $operation['summary'],
            ];
        }

        ksort($grouped);

        return [
            'count' => count($operations),
            'groups' => $grouped
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
            if (isset($sanitized['CLIENT_SECRET'])) {
                $sanitized['CLIENT_SECRET'] = '***HIDDEN***';
            }
            if (isset($sanitized['PASSWORD'])) {
                $sanitized['PASSWORD'] = '***HIDDEN***';
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
