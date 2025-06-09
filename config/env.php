<?php
// config/env.php
// ------------------------------------------------------------------
// Loads environment variables from .env into $_ENV,
// sets up global PHP error & exception handlers for DEBUG mode.
// ------------------------------------------------------------------

// Simple .env loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        // strip quotes
        if (strlen($val) > 1 && in_array($val[0], ['"', "'"])) {
            $val = substr($val, 1, -1);
        }
        $_ENV[$key] = $val;
    }
}

// Default DEBUG=false if not set
define('DEBUG', isset($_ENV['DEBUG']) && strtolower($_ENV['DEBUG']) === 'true');

// Log file path
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/debug.log';

// Include Logger
require_once __DIR__ . '/../src/Services/Logger.php';
Logger::init($logFile, DEBUG);

// Global error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Logger::handleError($errno, $errstr, $errfile, $errline);
});

// Global exception handler
set_exception_handler(function($ex) {
    Logger::handleException($ex);
});

// Shutdown function for fatal errors
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        Logger::handleError($err['type'], $err['message'], $err['file'], $err['line']);
        // If this happens before any output, force minimal HTML
        if (!headers_sent()) {
            http_response_code(500);
            echo '<div style="padding:20px;background:#300;color:#fff;font-family:monospace;">';
            echo '<h2>Fatal Error</h2><pre>' 
                 . htmlentities($err['message'].' in '.$err['file'].' on line '.$err['line'])
                 . '</pre></div>';
        }
    }
});
