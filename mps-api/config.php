<?php
/**
 * Configuration Loader
 * 
 * Loads environment variables from .env file
 * Subdirectory-safe path handling with enhanced security
 * 
 * @version 1.1.0 - Enhanced error handling and security
 */

// Prevent direct access - must be included by engine
if (!defined('MPS_ENGINE_ACCESS')) {
    http_response_code(403);
    die('Direct access not permitted');
}

/**
 * Load .env file and return configuration array with file locking
 */
function loadEnvironment() {
    $envPath = __DIR__ . '/.env';
    
    if (!file_exists($envPath)) {
        throw new Exception('.env file not found at: ' . $envPath);
    }
    
    if (!is_readable($envPath)) {
        throw new Exception('.env file not readable: ' . $envPath);
    }
    
    // Open file with shared lock for reading
    $handle = fopen($envPath, 'r');
    if ($handle === false) {
        throw new Exception('Failed to open .env file');
    }
    
    // Acquire shared lock
    if (!flock($handle, LOCK_SH)) {
        fclose($handle);
        throw new Exception('Failed to acquire lock on .env file');
    }
    
    $config = [];
    $lineNumber = 0;
    
    try {
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);
            
            // Skip empty lines
            if ($line === '') {
                continue;
            }
            
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') === false) {
                // Malformed line - log warning but continue
                error_log("Warning: Malformed .env line {$lineNumber}: {$line}");
                continue;
            }
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Validate key format (alphanumeric and underscore only)
            if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $key)) {
                error_log("Warning: Invalid .env key format on line {$lineNumber}: {$key}");
                continue;
            }
            
            // Remove quotes if present (handle escaped quotes)
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
                // Unescape escaped quotes
                $value = str_replace(['\"', "\'"], ['"', "'"], $value);
            }
            
            // Store in config
            $config[$key] = $value;
        }
    } finally {
        // Always release lock and close file
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    
    if (empty($config)) {
        throw new Exception('.env file is empty or contains no valid configuration');
    }
    
    return $config;
}

/**
 * Validate configuration has required fields
 */
function validateConfig($config) {
    if (!is_array($config)) {
        throw new Exception('Configuration must be an array');
    }
    
    $required = ['MPS_BASE_URL', 'MPS_API_KEY'];
    $missing = [];
    
    foreach ($required as $key) {
        if (!isset($config[$key]) || trim($config[$key]) === '') {
            $missing[] = $key;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Missing required configuration: ' . implode(', ', $missing));
    }
    
    // Validate URL format
    if (!filter_var($config['MPS_BASE_URL'], FILTER_VALIDATE_URL)) {
        throw new Exception('MPS_BASE_URL must be a valid URL');
    }
    
    // Validate URL uses HTTPS in production
    $parsedUrl = parse_url($config['MPS_BASE_URL']);
    if (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] !== 'https') {
        // Log warning but don't fail (allow HTTP for local dev)
        error_log('Warning: MPS_BASE_URL should use HTTPS in production');
    }
    
    // Validate API key is not obviously a placeholder
    $invalidKeys = ['your_api_key_here', 'your_actual_api_key', 'test', 'demo', 'placeholder'];
    $apiKeyLower = strtolower($config['MPS_API_KEY']);
    foreach ($invalidKeys as $invalid) {
        if (strpos($apiKeyLower, $invalid) !== false) {
            throw new Exception('MPS_API_KEY appears to be a placeholder value');
        }
    }
    
    // Validate API key minimum length
    if (strlen($config['MPS_API_KEY']) < 10) {
        throw new Exception('MPS_API_KEY is too short (minimum 10 characters)');
    }
    
    return true;
}

/**
 * Sanitize and set default values
 */
function setDefaults($config) {
    // Timeout values
    $config['MPS_TIMEOUT'] = isset($config['MPS_TIMEOUT']) ? 
        (int)$config['MPS_TIMEOUT'] : 30;
    
    $config['MPS_CONNECT_TIMEOUT'] = isset($config['MPS_CONNECT_TIMEOUT']) ? 
        (int)$config['MPS_CONNECT_TIMEOUT'] : 10;
    
    // Validate timeout ranges
    if ($config['MPS_TIMEOUT'] < 1) {
        $config['MPS_TIMEOUT'] = 30;
    }
    if ($config['MPS_TIMEOUT'] > 300) {
        $config['MPS_TIMEOUT'] = 300;
    }
    
    if ($config['MPS_CONNECT_TIMEOUT'] < 1) {
        $config['MPS_CONNECT_TIMEOUT'] = 10;
    }
    if ($config['MPS_CONNECT_TIMEOUT'] > 60) {
        $config['MPS_CONNECT_TIMEOUT'] = 60;
    }
    
    // Debug mode
    $config['MPS_DEBUG'] = isset($config['MPS_DEBUG']) ? 
        filter_var($config['MPS_DEBUG'], FILTER_VALIDATE_BOOLEAN) : false;
    
    // Max retries
    $config['MPS_MAX_RETRIES'] = isset($config['MPS_MAX_RETRIES']) ? 
        (int)$config['MPS_MAX_RETRIES'] : 3;
    
    if ($config['MPS_MAX_RETRIES'] < 0) {
        $config['MPS_MAX_RETRIES'] = 0;
    }
    if ($config['MPS_MAX_RETRIES'] > 10) {
        $config['MPS_MAX_RETRIES'] = 10;
    }
    
    return $config;
}

// Main configuration loading logic
try {
    $config = loadEnvironment();
    validateConfig($config);
    $config = setDefaults($config);
    
    return $config;
    
} catch (Exception $e) {
    // Log error to multiple locations
    $logDir = __DIR__ . '/logs';
    
    // Ensure log directory exists
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    // Log to config-specific error file
    if (is_dir($logDir) && is_writable($logDir)) {
        $logFile = $logDir . '/config_error_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Configuration Error: {$e->getMessage()}\n";
        
        // Include stack trace in debug mode
        if (getenv('MPS_DEBUG') === 'true') {
            $logMessage .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
        
        @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    // Also log to PHP error log
    error_log('MPS API Engine Configuration Error: ' . $e->getMessage());
    
    // Re-throw exception for engine to handle
    throw $e;
}
