<?php
/**
 * MPS Monitors API Engine - Main Entry Point with Advanced Diagnostics
 *
 * Version 1.1.0 - Enhanced error handling, diagnostics, and troubleshooting
 * Domain: https://mpsm.resolutionsbydesign.us
 *
 * Features:
 * - Comprehensive diagnostic information
 * - Detailed error reporting (respects debug mode)
 * - System health checks
 * - OAuth status monitoring
 * - File system verification
 * - Performance metrics
 */

// Security constant for includes
define('MPS_ENGINE_ACCESS', true);

// Capture start time for performance metrics
define('REQUEST_START_TIME', microtime(true));

// Load engine early (required for all routes except diagnostics)
require_once __DIR__ . '/engine.php';

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);

// Set error log path
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php_errors_' . date('Y-m-d') . '.log');

// Global error storage for diagnostics
$GLOBALS['startup_errors'] = [];
$GLOBALS['startup_warnings'] = [];

// Custom error handler to capture all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'time' => date('Y-m-d H:i:s')
    ];

    if ($errno === E_WARNING || $errno === E_USER_WARNING) {
        $GLOBALS['startup_warnings'][] = $error;
    } else {
        $GLOBALS['startup_errors'][] = $error;
    }

    // Log to file
    error_log(sprintf('[%s] %s in %s:%d',
        date('Y-m-d H:i:s'), $errstr, $errfile, $errline));

    return false; // Allow default error handler to run
});

// Request size limit (protect against memory exhaustion)
define('MAX_REQUEST_SIZE', 1048576); // 1MB

// Rate limiting (simple implementation)
define('MAX_REQUESTS_PER_MINUTE', 60);

/**
 * System Diagnostics - Check environment and dependencies
 */
function getSystemDiagnostics() {
    $diag = [
        'timestamp' => date('c'),
        'php' => [
            'version' => phpversion(),
            'sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'extensions' => [
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
            ],
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'display_errors' => ini_get('display_errors'),
            'error_log' => ini_get('error_log'),
        ],
        'server' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ],
        'filesystem' => [
            'current_dir' => __DIR__,
            'writable' => is_writable(__DIR__),
            'logs_dir_exists' => is_dir(__DIR__ . '/logs'),
            'logs_dir_writable' => is_writable(__DIR__ . '/logs'),
        ],
        'files' => [
            'index.php' => file_exists(__DIR__ . '/index.php'),
            'engine.php' => file_exists(__DIR__ . '/engine.php'),
            'SwaggerActionRegistry.php' => file_exists(__DIR__ . '/SwaggerActionRegistry.php'),
            'payload_templates.php' => file_exists(__DIR__ . '/payload_templates.php'),
            'Swagger.json' => file_exists(__DIR__ . '/Swagger.json') || file_exists(__DIR__ . '/swagger.json'),
            '.env' => file_exists(__DIR__ . '/.env'),
        ],
        'file_sizes' => [],
        'file_permissions' => [],
    ];

    // Get file sizes and permissions
    foreach ($diag['files'] as $file => $exists) {
        $path = __DIR__ . '/' . $file;
        if ($exists) {
            $diag['file_sizes'][$file] = filesize($path);
            $diag['file_permissions'][$file] = substr(sprintf('%o', fileperms($path)), -4);
        }
    }

    // Check Swagger.json validity (case-insensitive)
    if ($diag['files']['Swagger.json']) {
        // Check both uppercase and lowercase versions
        $swaggerPath = file_exists(__DIR__ . '/Swagger.json') ? __DIR__ . '/Swagger.json' : __DIR__ . '/swagger.json';
        $swaggerContent = @file_get_contents($swaggerPath);
        if ($swaggerContent !== false) {
            $swaggerData = @json_decode($swaggerContent, true);
            $diag['swagger'] = [
                'file_used' => basename($swaggerPath),
                'valid_json' => (json_last_error() === JSON_ERROR_NONE),
                'size_bytes' => strlen($swaggerContent),
                'has_paths' => isset($swaggerData['paths']),
                'path_count' => isset($swaggerData['paths']) ? count($swaggerData['paths']) : 0,
            ];
        } else {
            $diag['swagger'] = ['error' => 'Failed to read Swagger.json'];
        }
    }

    // Check .env file
    if ($diag['files']['.env']) {
        $envContent = @file_get_contents(__DIR__ . '/.env');
        if ($envContent !== false) {
            $lines = explode("\n", $envContent);
            $configKeys = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    $parts = explode('=', $line, 2);
                    $key = trim($parts[0]);
                    $configKeys[] = $key;
                }
            }
            $diag['env_config'] = [
                'readable' => true,
                'line_count' => count($lines),
                'config_keys_found' => $configKeys,
                'has_required' => [
                    'CLIENT_ID' => in_array('CLIENT_ID', $configKeys),
                    'CLIENT_SECRET' => in_array('CLIENT_SECRET', $configKeys),
                    'USERNAME' => in_array('USERNAME', $configKeys),
                    'PASSWORD' => in_array('PASSWORD', $configKeys),
                    'DEALER_CODE' => in_array('DEALER_CODE', $configKeys),
                    'API_BASE_URL' => in_array('API_BASE_URL', $configKeys),
                ]
            ];
        } else {
            $diag['env_config'] = ['error' => 'Failed to read .env file'];
        }
    } else {
        $diag['env_config'] = ['error' => '.env file not found'];
    }

    return $diag;
}

/**
 * Engine Diagnostics - Test engine initialization
 */
function getEngineDiagnostics() {
    $diag = [
        'initialization' => 'not_attempted',
        'error' => null,
        'engine_loaded' => false,
        'registry_loaded' => false,
    ];

    try {
        // Try to load engine
        if (file_exists(__DIR__ . '/engine.php')) {
            require_once __DIR__ . '/engine.php';
            $diag['engine_loaded'] = class_exists('MPSMonitorEngine');

            if ($diag['engine_loaded']) {
                try {
                    $engine = MPSMonitorEngine::getInstance();
                    $diag['initialization'] = 'success';

                    // Get engine stats
                    $stats = MPSMonitorEngine::getStats();
                    $diag['stats'] = $stats;

                    // Try health check
                    try {
                        $health = $engine->healthCheck();
                        $diag['health'] = $health;
                    } catch (Exception $e) {
                        $diag['health_error'] = [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ];
                    }
                } catch (Exception $e) {
                    $diag['initialization'] = 'failed';
                    $diag['error'] = [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ];
                }
            }
        } else {
            $diag['error'] = 'engine.php file not found';
        }

        // Try to load registry
        if (file_exists(__DIR__ . '/SwaggerActionRegistry.php')) {
            require_once __DIR__ . '/SwaggerActionRegistry.php';
            $diag['registry_loaded'] = class_exists('SwaggerActionRegistry');

            if ($diag['registry_loaded']) {
                try {
                    $registry = SwaggerActionRegistry::getInstance();
                    $operations = $registry->listOperations();
                    $diag['registry_operations'] = count($operations);
                } catch (Exception $e) {
                    $diag['registry_error'] = [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        $diag['initialization'] = 'critical_error';
        $diag['error'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
    }

    return $diag;
}

/**
 * Send JSON response with comprehensive error information
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    // Add standard response fields
    if (!isset($data['timestamp'])) {
        $data['timestamp'] = date('c');
    }

    // Add performance metrics
    if (defined('REQUEST_START_TIME')) {
        $data['performance'] = [
            'duration_ms' => round((microtime(true) - REQUEST_START_TIME) * 1000, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];
    }

    // Check debug mode
    $isDebug = (getenv('MPS_DEBUG') === 'true' || getenv('MPS_DEBUG') === '1');

    // Add diagnostics in debug mode or on errors
    if ($isDebug || $statusCode >= 400) {
        if (!empty($GLOBALS['startup_errors'])) {
            $data['startup_errors'] = $GLOBALS['startup_errors'];
        }
        if (!empty($GLOBALS['startup_warnings']) && $isDebug) {
            $data['startup_warnings'] = $GLOBALS['startup_warnings'];
        }
    }

    // Sanitize error messages in production (hide internal details)
    if (!$isDebug && isset($data['error']) && is_string($data['error'])) {
        // Remove file paths and line numbers from error messages
        $data['error'] = preg_replace('/in \/.*\.php on line \d+/', '', $data['error']);
        $data['error'] = preg_replace('/\/[^ ]+\.php/', 'file.php', $data['error']);

        // Remove sensitive context in production
        unset($data['error_detail']);
        unset($data['raw_response']);
        unset($data['context']);
        unset($data['startup_errors']);
        unset($data['startup_warnings']);
    }

    // Encode JSON with error handling
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        // JSON encoding failed
        $jsonError = json_last_error_msg();
        error_log("JSON encoding error: {$jsonError}");

        http_response_code(500);
        $json = json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'timestamp' => date('c')
        ]);
    }

    echo $json;
    exit;
}

// Validate request size before processing
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > MAX_REQUEST_SIZE) {
    sendResponse([
        'success' => false,
        'error' => 'Request too large',
        'max_size' => MAX_REQUEST_SIZE
    ], 413);
}

// Simple rate limiting (file-based)
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitFile = __DIR__ . '/logs/ratelimit_' . date('Y-m-d-H-i') . '.log';

    // Clean up old rate limit files (older than 2 minutes)
    $files = glob(__DIR__ . '/logs/ratelimit_*.log');
    $twoMinutesAgo = time() - 120;
    foreach ($files as $file) {
        if (filemtime($file) < $twoMinutesAgo) {
            @unlink($file);
        }
    }

    // Count requests from this IP in the current minute
    if (file_exists($rateLimitFile)) {
        $contents = file_get_contents($rateLimitFile);
        $requests = substr_count($contents, $ip);

        if ($requests >= MAX_REQUESTS_PER_MINUTE) {
            sendResponse([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'retry_after' => 60
            ], 429);
        }
    }

    // Log this request
    @file_put_contents($rateLimitFile, $ip . "\n", FILE_APPEND | LOCK_EX);
}

// Apply rate limiting
checkRateLimit();

// Validate request method
$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
    sendResponse([
        'success' => false,
        'error' => 'Method not allowed',
        'allowed_methods' => $allowedMethods
    ], 405);
}

// CORS Headers (configure for production)
$allowedOrigins = ['*']; // Change to specific domain in production
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($allowedOrigins[0] === '*') {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-ID');
header('Access-Control-Max-Age: 86400');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Get base path for subdirectory deployment
 */
function getBasePath() {
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($scriptName, '/');
}

/**
 * Get request path relative to subdirectory with security validation
 */
function getRequestPath() {
    $basePath = getBasePath();
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($requestUri === false || $requestUri === null) {
        return '/';
    }

    // Security: Decode and validate path
    $requestUri = rawurldecode($requestUri);

    // Security: Block path traversal attempts
    if (strpos($requestUri, '..') !== false) {
        logSecurityEvent('Path traversal attempt detected', [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        sendResponse(['error' => 'Invalid path'], 400);
    }

    // Security: Block null bytes
    if (strpos($requestUri, "\0") !== false) {
        logSecurityEvent('Null byte injection attempt', [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        sendResponse(['error' => 'Invalid path'], 400);
    }

    // Remove base path from request URI
    if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }

    return '/' . trim($requestUri, '/');
}

/**
 * Get request body with size validation and error handling
 */
function getRequestBody() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') === false && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        logSecurityEvent('Invalid content type', [
            'content_type' => $contentType,
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
    }

    // Read input with size limit
    $body = '';
    $input = fopen('php://input', 'r');

    if ($input === false) {
        return [];
    }

    $bytesRead = 0;
    while (!feof($input) && $bytesRead < MAX_REQUEST_SIZE) {
        $chunk = fread($input, 8192);
        if ($chunk === false) {
            break;
        }
        $body .= $chunk;
        $bytesRead += strlen($chunk);

        if ($bytesRead >= MAX_REQUEST_SIZE) {
            fclose($input);
            sendResponse(['error' => 'Request body too large'], 413);
        }
    }

    fclose($input);

    if (empty($body)) {
        return [];
    }

    // Parse JSON
    $decoded = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = json_last_error_msg();
        logSecurityEvent('Invalid JSON in request', [
            'error' => $error,
            'body_preview' => substr($body, 0, 100)
        ]);

        sendResponse([
            'success' => false,
            'error' => 'Invalid JSON in request body',
            'detail' => $error
        ], 400);
    }

    return $decoded ?? [];
}

/**
 * Validate required fields in request
 */
function validateRequiredFields($data, $requiredFields) {
    $missing = [];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        sendResponse([
            'success' => false,
            'error' => 'Missing required fields: ' . implode(', ', $missing),
            'required_fields' => $requiredFields
        ], 400);
    }

    return true;
}

/**
 * Log security events
 */
function logSecurityEvent($message, $context = []) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        return;
    }

    $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');

    $logEntry = "[{$timestamp}] SECURITY: {$message}";
    if (!empty($context)) {
        $logEntry .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES);
    }
    $logEntry .= "\n";

    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function statusFromEngineResult(array $result): int {
    if (!isset($result['success']) || $result['success'] === true) {
        return 200;
    }

    if (isset($result['http_code']) && is_numeric($result['http_code']) && $result['http_code'] >= 100) {
        return (int) $result['http_code'];
    }

    $code = $result['error_code'] ?? null;
    switch ($code) {
        case MPSMonitorEngine::ERR_VALIDATION:
            return 400;
        case MPSMonitorEngine::ERR_NETWORK:
            return 502;
        case MPSMonitorEngine::ERR_CONFIG:
        case MPSMonitorEngine::ERR_INTERNAL:
            return 500;
        case MPSMonitorEngine::ERR_API:
            return 502;
        default:
            return 400;
    }
}

// Main routing logic with comprehensive error handling
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getRequestPath();
    $basePath = getBasePath();

    // Route: Diagnostics (always available, even if engine fails)
    // ALWAYS shows full diagnostic data - no debug mode needed for troubleshooting
    if ($path === '/diagnostics' || $path === '/debug') {
        $isDebug = (getenv('MPS_DEBUG') === 'true' || getenv('MPS_DEBUG') === '1');

        $response = [
            'status' => 'diagnostics',
            'debug_mode' => $isDebug,
            'system' => getSystemDiagnostics(),
            'engine' => getEngineDiagnostics(),
        ];

        // ALWAYS add recent error logs for troubleshooting (not just in debug mode)
        $errorLog = __DIR__ . '/logs/php_errors_' . date('Y-m-d') . '.log';
        if (file_exists($errorLog)) {
            $logLines = @file($errorLog, FILE_IGNORE_NEW_LINES);
            if ($logLines !== false && count($logLines) > 0) {
                $response['recent_errors'] = array_slice($logLines, -50); // Last 50 lines
            }
        }

        // Check for .env.example to help with setup
        if (!$response['system']['files']['.env'] && file_exists(__DIR__ . '/.env.example')) {
            $response['setup_help'] = [
                'message' => '.env file is missing - copy from .env.example',
                'command' => 'cp .env.example .env && nano .env'
            ];
        }

        // Check for Swagger.json in parent directory
        if (!$response['system']['files']['Swagger.json']) {
            $parentSwagger = dirname(__DIR__) . '/Swagger.json';
            $response['file_search'] = [
                'checking_parent_directory' => $parentSwagger,
                'exists_in_parent' => file_exists($parentSwagger)
            ];
            if (file_exists($parentSwagger)) {
                $response['setup_help']['swagger_location'] = 'Swagger.json found in parent directory - copy to mps-api/';
            }
        }

        sendResponse($response, 200);
    }

    // Load engine (required for all other routes)
    try {
        $engine = MPSMonitorEngine::getInstance();
    } catch (Throwable $e) {
        // Engine failed to load - send detailed error
        sendResponse([
            'success' => false,
            'error' => 'Failed to initialize API engine',
            'error_code' => 500,
            'details' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ],
            'suggestion' => 'Check /diagnostics endpoint for full system status'
        ], 500);
    }

    // Route: Health check / Root
    if ($path === '/' || $path === '') {
        $registry = SwaggerActionRegistry::getInstance();
        $operations = $registry->listOperations();
        $sampleActions = array_slice(array_map(function ($op) {
            return $op['action'];
        }, $operations), 0, 10);

        sendResponse([
            'status' => 'online',
            'service' => 'MPS Monitors API Engine',
            'version' => '1.1.0',
            'fixes_applied' => [
                'oauth_all_endpoints' => true,
                'smart_parameter_population' => true,
                'mpsm_response_validation' => true,
                'hard_coded_dealer_defaults' => true,
            ],
            'timestamp' => date('c'),
            'base_path' => $basePath,
            'action_count' => count($operations),
            'sample_actions' => $sampleActions,
            'endpoints' => [
                'health' => $basePath . '/health',
                'diagnostics' => $basePath . '/diagnostics',
                'endpoints' => $basePath . '/endpoints',
                'query' => $basePath . '/query',
                'swagger' => $basePath . '/swagger.json',
                'chatgpt_integration' => $basePath . '/chatgpt-schema'
            ],
            'stats' => MPSMonitorEngine::getStats(),
            'api_interaction_data' => [
                'oauth_endpoint' => getenv('TOKEN_URL') ?: 'https://api.abassetmanagement.com/api3/token',
                'api_base_url' => getenv('API_BASE_URL') ?: 'https://api.abassetmanagement.com/api3/',
                'auth_method' => 'OAuth 2.0 Password Grant',
                'dealer_code' => 'NY06AGDWUQ',
                'dealer_id' => 'SZ13qRwU5GtFLj0i_CbEgQ2',
                'request_format' => 'POST ' . $basePath . '/query with {"action": "ActionName", "params": {...}}',
                'payload_templates' => '158 discovered endpoint patterns loaded',
            ],
            'chatgpt_integration' => [
                'status' => 'ready',
                'schema_endpoint' => $basePath . '/chatgpt-schema',
                'query_endpoint' => $basePath . '/query',
                'documentation' => 'See /chatgpt-schema for full OpenAPI schema'
            ]
        ]);
    }

    // Route: Health check
    if ($path === '/health') {
        $health = $engine->healthCheck();
        sendResponse($health);
    }

    // Route: Dashboard (HTML interface)
    if ($path === '/dashboard' || $path === '/dashboard.html') {
        $dashboardFile = __DIR__ . '/dashboard.html';
        if (file_exists($dashboardFile)) {
            header('Content-Type: text/html');
            readfile($dashboardFile);
            exit;
        } else {
            sendResponse(['error' => 'Dashboard not found'], 404);
        }
    }

    // Route: Available endpoints
    if ($path === '/endpoints') {
        $registry = SwaggerActionRegistry::getInstance();
        $operations = array_map(function (array $op) {
            return [
                'action' => $op['action'],
                'method' => $op['method'],
                'path' => '/' . ltrim($op['path'], '/'),
                'summary' => $op['summary'],
                'aliases' => $op['aliases'],
            ];
        }, $registry->listOperations());
        $groups = $engine->getAvailableEndpoints();

        sendResponse([
            'success' => true,
            'base_url' => $basePath,
            'count' => count($operations),
            'operations' => $operations,
            'groups' => $groups['groups'] ?? [],
            'documentation' => $basePath . '/swagger.json'
        ]);
    }

    // Route: Swagger JSON
    if ($path === '/swagger.json') {
        $registry = SwaggerActionRegistry::getInstance();
        $swaggerPath = $registry->getSpecPath();
        if (!file_exists($swaggerPath)) {
            sendResponse(['error' => 'Swagger documentation not found'], 404);
        }

        if (!is_readable($swaggerPath)) {
            sendResponse(['error' => 'Swagger documentation not readable'], 500);
        }

        $swagger = file_get_contents($swaggerPath);
        if ($swagger === false) {
            sendResponse(['error' => 'Failed to read swagger documentation'], 500);
        }

        // Validate JSON
        $decoded = json_decode($swagger);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendResponse(['error' => 'Invalid swagger JSON'], 500);
        }

        header('Content-Type: application/json');
        echo $swagger;
        exit;
    }

    // Route: ChatGPT Custom API Integration Schema
    if ($path === '/chatgpt-schema') {
        $schema = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'MPS Monitors API',
                'description' => 'MPS Monitors API integration for ChatGPT Custom Actions. Provides access to 544 MPSM API endpoints with automatic OAuth authentication and smart parameter population.',
                'version' => '1.1.0'
            ],
            'servers' => [
                [
                    'url' => 'https://mpsm.resolutionsbydesign.us/mps-api',
                    'description' => 'Production MPS API Engine'
                ]
            ],
            'paths' => [
                '/query' => [
                    'post' => [
                        'summary' => 'Execute any MPSM API action',
                        'description' => 'Universal endpoint for all 544 MPSM API actions. Automatically handles OAuth authentication, parameter population, and response formatting.',
                        'operationId' => 'executeAction',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['action'],
                                        'properties' => [
                                            'action' => [
                                                'type' => 'string',
                                                'description' => 'The API action to execute (e.g., "AlertLimit/Dealer/Get", "ApiClient/List", "CustomField/List")',
                                                'example' => 'AlertLimit/Dealer/Get'
                                            ],
                                            'params' => [
                                                'type' => 'object',
                                                'description' => 'Parameters for the action. Most parameters are auto-populated from templates. Dealer code (NY06AGDWUQ) is always used.',
                                                'additionalProperties' => true,
                                                'example' => []
                                            ]
                                        ]
                                    ],
                                    'examples' => [
                                        'get_dealer_alert_limits' => [
                                            'summary' => 'Get dealer alert limits',
                                            'value' => [
                                                'action' => 'AlertLimit/Dealer/Get',
                                                'params' => []
                                            ]
                                        ],
                                        'list_api_clients' => [
                                            'summary' => 'List API clients',
                                            'value' => [
                                                'action' => 'ApiClient/List',
                                                'params' => []
                                            ]
                                        ],
                                        'list_custom_fields' => [
                                            'summary' => 'List custom fields',
                                            'value' => [
                                                'action' => 'CustomField/List',
                                                'params' => []
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful response',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => [
                                                    'type' => 'boolean',
                                                    'description' => 'Whether the request was successful'
                                                ],
                                                'data' => [
                                                    'description' => 'The response data from the API',
                                                    'oneOf' => [
                                                        ['type' => 'object'],
                                                        ['type' => 'array']
                                                    ]
                                                ],
                                                'http_code' => [
                                                    'type' => 'integer',
                                                    'description' => 'HTTP status code from upstream API'
                                                ],
                                                'timestamp' => [
                                                    'type' => 'string',
                                                    'format' => 'date-time'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '/health' => [
                    'get' => [
                        'summary' => 'Health check',
                        'description' => 'Verify API engine and MPSM API connectivity',
                        'operationId' => 'healthCheck',
                        'responses' => [
                            '200' => [
                                'description' => 'Health status',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean'],
                                                'status' => [
                                                    'type' => 'string',
                                                    'enum' => ['healthy', 'degraded', 'unhealthy']
                                                ],
                                                'message' => ['type' => 'string']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '/endpoints' => [
                    'get' => [
                        'summary' => 'List all available actions',
                        'description' => 'Get a complete list of all 544 MPSM API actions',
                        'operationId' => 'listEndpoints',
                        'responses' => [
                            '200' => [
                                'description' => 'List of endpoints',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean'],
                                                'count' => ['type' => 'integer'],
                                                'operations' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'action' => ['type' => 'string'],
                                                            'method' => ['type' => 'string'],
                                                            'path' => ['type' => 'string'],
                                                            'summary' => ['type' => 'string']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'components' => [
                'schemas' => []
            ],
            'x-implementation-guide' => [
                'dealer_defaults' => [
                    'dealer_code' => 'NY06AGDWUQ',
                    'dealer_id' => 'SZ13qRwU5GtFLj0i_CbEgQ2',
                    'note' => 'These values are hard-coded and automatically used for all requests'
                ],
                'auto_population' => [
                    'enabled' => true,
                    'templates_loaded' => 158,
                    'description' => 'The engine automatically populates required parameters based on discovered working patterns',
                    'examples' => [
                        'dealer_codes' => 'Automatically filled from config',
                        'pagination' => 'pageNumber: 1, pageSize: 50',
                        'sorting' => 'sortOrder: Asc, sortColumn: Name',
                        'dates' => 'fromDate: -30 days, toDate: today'
                    ]
                ],
                'usage_examples' => [
                    [
                        'description' => 'Get dealer alert limits (no params needed)',
                        'request' => [
                            'action' => 'AlertLimit/Dealer/Get',
                            'params' => []
                        ]
                    ],
                    [
                        'description' => 'List API clients with pagination',
                        'request' => [
                            'action' => 'ApiClient/List',
                            'params' => []
                        ]
                    ],
                    [
                        'description' => 'Override default page size',
                        'request' => [
                            'action' => 'ApiClient/List',
                            'params' => [
                                'pageRows' => 100
                            ]
                        ]
                    ]
                ],
                'chatgpt_setup' => [
                    'step_1' => 'In ChatGPT, go to "Explore GPTs" > "Create"',
                    'step_2' => 'Configure > Actions > Import from URL',
                    'step_3' => 'Enter: https://mpsm.resolutionsbydesign.us/mps-api/chatgpt-schema',
                    'step_4' => 'Authentication: None (handled automatically)',
                    'step_5' => 'Test with: "Get dealer alert limits"'
                ]
            ]
        ];

        sendResponse($schema, 200);
    }

    // Route: Main query endpoint (ChatGPT Actions primary)
    if ($path === '/query' && $method === 'POST') {
        $body = getRequestBody();
        validateRequiredFields($body, ['action']);

        $action = is_string($body['action']) ? trim($body['action']) : '';
        if ($action === '') {
            sendResponse([
                'success' => false,
                'error' => 'Action name is required.'
            ], 400);
        }

        $params = $body['params'] ?? [];
        if (!is_array($params)) {
            sendResponse([
                'success' => false,
                'error' => 'Params must be an object.'
            ], 400);
        }

        if (strcasecmp($action, 'healthCheck') === 0) {
            $result = $engine->healthCheck();
            sendResponse($result);
        }

        $result = $engine->dispatchAction($action, $params);
        $status = statusFromEngineResult($result);
        sendResponse($result, $status);
    }

    // 404 - Route not found
    sendResponse([
        'error' => 'Endpoint not found',
        'path' => $path,
        'method' => $method,
        'available_endpoints' => [
            'root' => $basePath . '/',
            'health' => $basePath . '/health',
            'diagnostics' => $basePath . '/diagnostics',
            'endpoints' => $basePath . '/endpoints',
            'query' => $basePath . '/query',
            'swagger' => $basePath . '/swagger.json'
        ],
        'documentation' => $basePath . '/swagger.json'
    ], 404);

} catch (Exception $e) {
    // Log exception
    $errorCode = $e->getCode() ?: 500;
    $errorMessage = $e->getMessage();

    error_log("Unhandled exception [{$errorCode}]: {$errorMessage}");
    error_log("Stack trace: " . $e->getTraceAsString());

    // Send comprehensive error in debug mode
    $isDebug = (getenv('MPS_DEBUG') === 'true' || getenv('MPS_DEBUG') === '1');

    $response = [
        'success' => false,
        'error' => $isDebug ? $errorMessage : 'Internal server error',
        'error_code' => $errorCode,
        'timestamp' => date('c')
    ];

    if ($isDebug) {
        $response['exception'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ];
        $response['system'] = getSystemDiagnostics();
    }

    sendResponse($response, 500);
} catch (Throwable $e) {
    // Catch any PHP 7+ errors
    error_log("Critical error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    $isDebug = (getenv('MPS_DEBUG') === 'true' || getenv('MPS_DEBUG') === '1');

    sendResponse([
        'success' => false,
        'error' => $isDebug ? $e->getMessage() : 'Critical server error',
        'error_code' => 500,
        'type' => get_class($e),
        'file' => $isDebug ? $e->getFile() : null,
        'line' => $isDebug ? $e->getLine() : null,
    ], 500);
}
