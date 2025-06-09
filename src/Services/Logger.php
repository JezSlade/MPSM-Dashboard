<?php
// src/Services/Logger.php
// ------------------------------------------------------------------
// Central logger for errors & exceptions.
// Writes to file and, if DEBUG, appends debug info to any JSON output.
// ------------------------------------------------------------------

class Logger
{
    /** @var string */
    private static $filePath;

    /** @var bool */
    private static $debug;

    public static function init(string $filePath, bool $debug = false)
    {
        self::$filePath = $filePath;
        self::$debug = $debug;
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $msg = "[Error][$errno] $errstr in $errfile on line $errline";
        self::writeLog($msg);
        if (self::$debug) {
            self::injectJsonDebug(['error' => $msg]);
        }
        // Don’t execute PHP internal handler
        return true;
    }

    public static function handleException(Throwable $ex)
    {
        $msg = "[Exception] " . $ex->getMessage()
             . " in " . $ex->getFile()
             . " on line " . $ex->getLine()
             . "\nStack trace:\n" . $ex->getTraceAsString();
        self::writeLog($msg);
        if (self::$debug) {
            self::injectJsonDebug(['exception' => $msg]);
        }
        http_response_code(500);
        echo self::$debug ? json_encode(['_debug' => $msg]) : '';
        exit;
    }

    private static function writeLog(string $msg)
    {
        $time = date('Y-m-d H:i:s');
        file_put_contents(self::$filePath, "[$time] $msg\n", FILE_APPEND);
    }

    private static function injectJsonDebug(array $data)
    {
        // Only if headers indicate JSON
        if (headers_sent() === false && strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['_debug' => $data]);
            exit;
        }
    }
}
