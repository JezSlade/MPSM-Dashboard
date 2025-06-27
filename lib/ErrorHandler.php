<?php
class ErrorHandler {
    private static $logFile;
    private static $initialized = false;

    public static function initialize(): void {
        if (self::$initialized) return;
        
        // Set default constants if not defined
        if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
        if (!defined('DEBUG_LOG_FILE')) define('DEBUG_LOG_FILE', __DIR__ . '/../logs/debug.log');
        
        self::$logFile = DEBUG_LOG_FILE;
        self::$initialized = true;
        
        // Create log directory if needed
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        self::log("Error [$errno] $errstr in $errfile:$errline");
        if (DEBUG_MODE || in_array($errno, [E_USER_ERROR, E_RECOVERABLE_ERROR])) {
            self::display("Error: $errstr", $errfile, $errline);
        }
        return true;
    }

    public static function handleException(Throwable $e) {
        self::log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        self::display("Exception: " . $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        exit(1);
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::log("Shutdown error: " . $error['message'] . " in " . $error['file'] . ":" . $error['line']);
            self::display("Fatal error: " . $error['message'], $error['file'], $error['line']);
        }
    }

    private static function log(string $message) {
        file_put_contents(
            self::$logFile,
            "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL,
            FILE_APPEND
        );
    }

    private static function display(string $error, string $file, int $line, string $trace = '') {
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: sans-serif; background: #1a1a2e; color: #e0f7fa; padding: 2rem; }
                .error-container { background: rgba(30,30,45,0.8); border: 1px solid #f00; padding: 1rem; }
                pre { background: rgba(0,0,0,0.3); padding: 1rem; overflow-x: auto; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h2>System Error</h2>
                <p><?= htmlspecialchars($error) ?></p>
                <p>File: <?= htmlspecialchars("$file:$line") ?></p>
                <?php if (DEBUG_MODE && $trace): ?>
                <h3>Stack Trace:</h3>
                <pre><?= htmlspecialchars($trace) ?></pre>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}