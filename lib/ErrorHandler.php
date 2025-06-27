<?php
class ErrorHandler {
    private static $logFile;
    private static $maxLogSize;

    public static function initialize(): void {
        self::$logFile = defined('DEBUG_LOG_FILE') ? DEBUG_LOG_FILE : __DIR__ . '/../logs/error.log';
        self::$maxLogSize = defined('MAX_LOG_SIZE_MB') ? MAX_LOG_SIZE_MB * 1024 * 1024 : 10 * 1024 * 1024;
        
        if (!file_exists(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    private static function rotateLogs(): void {
        if (file_exists(self::$logFile)) {
            clearstatcache(true, self::$logFile);
            if (@filesize(self::$logFile) >= self::$maxLogSize) {
                $backupFile = self::$logFile . '.' . date('YmdHis');
                @rename(self::$logFile, $backupFile);
                file_put_contents(self::$logFile, "Log rotated at " . date('Y-m-d H:i:s') . PHP_EOL);
            }
        }
    }

    public static function handleError(int $code, string $message, string $file, int $line): bool {
        self::rotateLogs();
        
        $error = new ErrorException($message, 0, $code, $file, $line);
        self::logException($error);
        
        if (DEBUG_MODE) {
            self::displayError($error);
        }
        
        return true;
    }

    public static function handleException(Throwable $e): void {
        self::logException($e);
        self::displayError($e);
    }

    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private static function logException(Throwable $e): void {
        $message = sprintf(
            "[%s] %s in %s:%d\nStack Trace:\n%s\n\n",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        $fp = fopen(self::$logFile, 'a');
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, $message);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    private static function displayError(Throwable $e): void {
        if (php_sapi_name() === 'cli') {
            echo "Error: {$e->getMessage()}\n";
            return;
        }
        
        http_response_code(500);
        include __DIR__ . '/../templates/error.php';
        exit;
    }
}

ErrorHandler::initialize();