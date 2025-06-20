<?php declare(strict_types=1);
/**
 *  error_bootstrap.php
 *
 *  • Enables strict error reporting.
 *  • Converts all PHP warnings/notices into ErrorException (Throwable).
 *  • Registers a shutdown handler to log true fatals (e.g. OOM, syntax).
 *  • Requires that api_functions.php (or any file that defines log_debug)
 *    is loaded *before* the first fatal occurs; if not, a minimal fallback
 *    logger writes to /logs/debug.log.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');   // keep UI clean; we log instead

// 1 ▸ guarantee a log_debug() even if helpers aren’t loaded yet
if (!function_exists('log_debug')) {
    function log_debug(string $scope, string $msg): void
    {
        $logDir  = __DIR__ . '/../logs/';
        $logFile = $logDir . 'debug.log';
        if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
        @file_put_contents(
            $logFile,
            '[' . date('c') . "] [$scope] $msg\n",
            FILE_APPEND | LOCK_EX
        );
    }
}

// 2 ▸ upgrade all PHP errors to exceptions we can catch
set_error_handler(
    static function (int $severity, string $message, string $file, int $line): bool {
        // Respect @ operator
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

// 3 ▸ capture fatal errors that bypass the handler
register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
        log_debug('FATAL', $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
        // In CARD_SANDBOX context emit a visible placeholder
        if (defined('CARD_SANDBOX') && CARD_SANDBOX) {
            echo '<div class="card-error" style="
                    background:rgba(255,0,0,.05);
                    border-radius:12px;
                    padding:1rem;
                    font-size:.9rem;
                    font-style:italic;
                    color:#ff6b6b;
                    text-align:center;
                 ">
                    ⚠ Fatal error: ' . htmlspecialchars($err['message'], ENT_QUOTES, 'UTF-8') . '
                 </div>';
        }
    }
});
