<?php declare(strict_types=1);
/**
 * Central debug helper. Logs all PHP warnings/notices/fatals to logs/debug.log
 * and provides appendDebug() which logs only when DEBUG_MODE=true.
 */

$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      $logFile);
error_reporting(E_ALL);

function appendDebug(string $msg): void
{
    if (getenv('DEBUG_MODE') === 'true') {
        error_log('[DBG] ' . $msg);
    }
}

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    error_log("[PHP][$severity] $message in $file:$line");
    return true;
});
set_exception_handler(function (Throwable $e): void {
    error_log('[EXCEPTION] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
});
register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[FATAL] ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
    }
});

appendDebug('debug.php loaded');
