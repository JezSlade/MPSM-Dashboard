<?php
/**
 * MPS Monitors API Engine - Main Entry Point
 * 
 * Subdirectory-safe router for API requests
 * Optimized for ChatGPT Actions and Dashboard integration
 * 
 * @version 1.1.0 - Enhanced security and error handling
 */

// Security constant for includes
define('MPS_ENGINE_ACCESS', true);

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

// Request size limit (protect against memory exhaustion)
define('MAX_REQUEST_SIZE', 1048576); // 1MB

// Rate limiting (simple implementation)
define('MAX_REQUESTS_PER_MINUTE', 60);

// Validate request size before processing
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > MAX_REQUEST_SIZE) {
    http_response_code(413);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Request too large',
        'max_size' => MAX_REQUEST_SIZE
    ]);
    exit;
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
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: 60');
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'retry_after' => 60
            ]);
            exit;
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
    http_response_code(405);
    header('Content-Type: application/json');
    header('Allow: ' . implode(', ', $allowedMethods));
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
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
header('Content-Type: application/json; charset=utf-8');

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

// Load engine
require_once __DIR__ . '/engine.php';

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
        http_response_code(400);
        sendResponse(['error' => 'Invalid path'], 400);
    }
    
    // Security: Block null bytes
    if (strpos($requestUri, "\0") !== false) {
        logSecurityEvent('Null byte injection attempt', [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        http_response_code(400);
        sendResponse(['error' => 'Invalid path'], 400);
    }
    
    // Remove base path from request URI
    if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    
    return '/' . trim($requestUri, '/');
}

/**
 * Send JSON response with proper headers and error handling
 */
function sendResponse($data, $statusCode = 200) {
    // Validate status code
    if (!is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 500;
    }
    
    http_response_code($statusCode);
    
    // Add standard response fields
    if (!isset($data['timestamp'])) {
        $data['timestamp'] = date('c');
    }
    
    // Sanitize error messages in production (hide internal details)
    $isProduction = (getenv('MPS_DEBUG') !== 'true');
    if ($isProduction && isset($data['error']) && is_string($data['error'])) {
        // Remove file paths and line numbers from error messages
        $data['error'] = preg_replace('/in \/.*\.php on line \d+/', '', $data['error']);
        $data['error'] = preg_replace('/\/[^ ]+\.php/', 'file.php', $data['error']);
        
        // Remove sensitive context in production
        unset($data['error_detail']);
        unset($data['raw_response']);
        unset($data['context']);
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

/**
 * Get request body with size validation and error handling
 */
function getRequestBody() {
    // Check content type
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
        
        // Check if exceeded limit
        if ($bytesRead >= MAX_REQUEST_SIZE) {
            fclose($input);
            http_response_code(413);
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
        
        http_response_code(400);
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

/**
 * Sanitize user input
 */
function sanitizeInput($input, $type = 'string') {
    if ($type === 'string') {
        return is_string($input) ? trim($input) : '';
    }
    if ($type === 'int') {
        return (int)$input;
    }
    if ($type === 'bool') {
        return (bool)$input;
    }
    if ($type === 'array') {
        return is_array($input) ? $input : [];
    }
    return $input;
}

// Main routing logic with comprehensive error handling
try {
    $engine = MPSMonitorEngine::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getRequestPath();
    $basePath = getBasePath();
    
    // Route: Health check / Root
    if ($path === '/' || $path === '') {
        sendResponse([
            'status' => 'online',
            'service' => 'MPS Monitors API Engine',
            'version' => '1.1.0',
            'timestamp' => date('c'),
            'base_path' => $basePath,
            'endpoints' => [
                'health' => $basePath . '/health',
                'endpoints' => $basePath . '/endpoints',
                'query' => $basePath . '/query',
                'swagger' => $basePath . '/swagger.json'
            ],
            'stats' => MPSMonitorEngine::getStats()
        ]);
    }
    
    // Route: Health check
    if ($path === '/health') {
        $health = $engine->healthCheck();
        sendResponse($health);
    }
    
    // Route: Available endpoints
    if ($path === '/endpoints') {
        $endpoints = $engine->getAvailableEndpoints();
        sendResponse([
            'success' => true,
            'base_url' => $basePath,
            'endpoints' => $endpoints,
            'documentation' => $basePath . '/swagger.json'
        ]);
    }
    
    // Route: Swagger JSON
    if ($path === '/swagger.json') {
        $swaggerPath = __DIR__ . '/swagger.json';
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
    
    // Route: Main query endpoint (ChatGPT Actions primary)
    if ($path === '/query' && $method === 'POST') {
        $body = getRequestBody();
        
        // Validate required fields
        validateRequiredFields($body, ['action']);
        
        $action = sanitizeInput($body['action'], 'string');
        $params = sanitizeInput($body['params'] ?? [], 'array');
        
        // Validate action format
        if (!preg_match('/^[a-zA-Z]+$/', $action)) {
            sendResponse([
                'success' => false,
                'error' => 'Invalid action format'
            ], 400);
        }
        
        // Route to appropriate engine method
        switch ($action) {
            case 'getMonitors':
                $result = $engine->getMonitors($params);
                break;
                
            case 'getMonitor':
                validateRequiredFields($params, ['id']);
                $result = $engine->getMonitor($params['id']);
                break;
                
            case 'createMonitor':
                validateRequiredFields($params, ['name', 'url']);
                $result = $engine->createMonitor($params);
                break;
                
            case 'updateMonitor':
                validateRequiredFields($params, ['id']);
                $monitorId = $params['id'];
                unset($params['id']);
                $result = $engine->updateMonitor($monitorId, $params);
                break;
                
            case 'deleteMonitor':
                validateRequiredFields($params, ['id']);
                $result = $engine->deleteMonitor($params['id']);
                break;
                
            case 'getAlerts':
                $result = $engine->getAlerts($params);
                break;
                
            case 'getStatistics':
                validateRequiredFields($params, ['id']);
                $period = $params['period'] ?? '24h';
                $result = $engine->getStatistics($params['id'], $period);
                break;
                
            case 'healthCheck':
                $result = $engine->healthCheck();
                break;
                
            default:
                sendResponse([
                    'success' => false,
                    'error' => 'Unknown action: ' . $action,
                    'available_actions' => [
                        'getMonitors', 'getMonitor', 'createMonitor', 
                        'updateMonitor', 'deleteMonitor', 'getAlerts', 
                        'getStatistics', 'healthCheck'
                    ]
                ], 400);
        }
        
        sendResponse($result);
    }
    
    // Route: Direct monitor access
    if (preg_match('#^/monitors(/(.+))?$#', $path, $matches)) {
        $monitorId = isset($matches[2]) ? $matches[2] : null;
        
        // Security: Validate monitor ID if present
        if ($monitorId !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', $monitorId)) {
            logSecurityEvent('Invalid monitor ID format', [
                'id' => $monitorId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            sendResponse(['error' => 'Invalid monitor ID format'], 400);
        }
        
        if ($monitorId && $method === 'GET') {
            $result = $engine->getMonitor($monitorId);
            sendResponse($result);
        } elseif (!$monitorId && $method === 'GET') {
            // Sanitize GET parameters
            $filters = [];
            foreach ($_GET as $key => $value) {
                if (preg_match('/^[a-zA-Z_]+$/', $key)) {
                    $filters[$key] = sanitizeInput($value, 'string');
                }
            }
            $result = $engine->getMonitors($filters);
            sendResponse($result);
        } elseif (!$monitorId && $method === 'POST') {
            $data = getRequestBody();
            validateRequiredFields($data, ['name', 'url']);
            $result = $engine->createMonitor($data);
            sendResponse($result, 201);
        } elseif ($monitorId && $method === 'PUT') {
            $data = getRequestBody();
            if (empty($data)) {
                sendResponse(['error' => 'No update data provided'], 400);
            }
            $result = $engine->updateMonitor($monitorId, $data);
            sendResponse($result);
        } elseif ($monitorId && $method === 'DELETE') {
            $result = $engine->deleteMonitor($monitorId);
            sendResponse($result);
        } else {
            sendResponse(['error' => 'Invalid monitor endpoint combination'], 400);
        }
    }
    
    // Route: Alerts
    if ($path === '/alerts' && $method === 'GET') {
        $filters = [];
        foreach ($_GET as $key => $value) {
            if (preg_match('/^[a-zA-Z_]+$/', $key)) {
                $filters[$key] = sanitizeInput($value, 'string');
            }
        }
        $result = $engine->getAlerts($filters);
        sendResponse($result);
    }
    
    // Route: Statistics
    if (preg_match('#^/monitors/(.+)/statistics$#', $path, $matches)) {
        $monitorId = $matches[1];
        
        // Security: Validate monitor ID
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $monitorId)) {
            logSecurityEvent('Invalid monitor ID in statistics request', [
                'id' => $monitorId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            sendResponse(['error' => 'Invalid monitor ID format'], 400);
        }
        
        $period = isset($_GET['period']) ? sanitizeInput($_GET['period'], 'string') : '24h';
        $result = $engine->getStatistics($monitorId, $period);
        sendResponse($result);
    }
    
    // 404 - Route not found
    sendResponse([
        'error' => 'Endpoint not found',
        'path' => $path,
        'method' => $method,
        'available_endpoints' => $basePath . '/endpoints',
        'documentation' => $basePath . '/swagger.json'
    ], 404);
    
} catch (Exception $e) {
    // Log exception
    $errorCode = $e->getCode() ?: 500;
    $errorMessage = $e->getMessage();
    
    error_log("Unhandled exception [{$errorCode}]: {$errorMessage}");
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send user-friendly error
    $isProduction = (getenv('MPS_DEBUG') !== 'true');
    
    sendResponse([
        'success' => false,
        'error' => $isProduction ? 'Internal server error' : $errorMessage,
        'error_code' => $errorCode,
        'timestamp' => date('c')
    ], 500);
}
