<?php
// core/debug.php
// v1.0.0 — Logging and error/exception handling

/**
 * Write a message to the debug log.
 *
 * @param string $message
 * @param array  $context  Additional data to JSON-encode
 * @param string $level    e.g. DEBUG, INFO, ERROR
 */
function debug_log(string $message, array $context = [], string $level = 'DEBUG'): void
{
    $logFile = __DIR__ . '/../logs/debug.log';
    $time    = date('Y-m-d H:i:s');
    $ctx     = $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $entry   = "[$time] [$level] $message" . ($ctx ? " | $ctx" : '');
    @file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Capture PHP errors and write to debug.log, but don’t break the UI
set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
    debug_log("PHP Error: {$errstr} in {$errfile}:{$errline}", ['errno' => $errno], 'ERROR');
    return true; // prevent PHP internal handler
});

// Capture uncaught exceptions
set_exception_handler(function(Throwable $ex) {
    debug_log(
        "Uncaught Exception: " . $ex->getMessage(),
        [
            'file'  => $ex->getFile(),
            'line'  => $ex->getLine(),
            'trace' => $ex->getTraceAsString()
        ],
        'ERROR'
    );
});
