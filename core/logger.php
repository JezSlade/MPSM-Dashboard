<?php
/**
 * Logger for MPSM Dashboard
 */
class Logger {
    const LOG_DIR = 'logs';
    const ERROR_LOG = 'error.log';
    const INFO_LOG = 'info.log';
    const DEBUG_LOG = 'debug.log';
    
    /**
     * Log an error message
     * @param string $message
     */
    public static function error($message) {
        self::log($message, self::ERROR_LOG);
        error_log($message);
    }
    
    /**
     * Log an info message
     * @param string $message
     */
    public static function info($message) {
        self::log($message, self::INFO_LOG);
    }
    
    /**
     * Log a debug message
     * @param string $message
     */
    public static function debug($message) {
        self::log($message, self::DEBUG_LOG);
    }
    
    /**
     * Log a message to a file
     * @param string $message
     * @param string $file
     */
    private static function log($message, $file) {
        // Create log directory if it doesn't exist
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
        
        $log_file = self::LOG_DIR . '/' . $file;
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}
