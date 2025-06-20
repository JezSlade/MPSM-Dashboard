<?php declare(strict_types=1);
/**
 *  Card sandbox loader.
 *
 *  Wraps a card include() inside an output buffer and converts ALL PHP
 *  notices / warnings / fatals into Throwable so we can catch them,
 *  write them to the debug log, and show a friendly placeholder in
 *  the UI instead of blank-paging the whole dashboard.
 *
 *  Usage:
 *      echo render_card(__DIR__.'/../cards/card_device_counters.php');
 */

if (!function_exists('log_debug')) {
    // Fallback: simple file write if helper not loaded yet
    function log_debug(string $scope, string $msg): void {
        $logDir  = __DIR__ . '/../logs/';
        $logFile = $logDir . 'debug.log';
        if (!is_dir($logDir)) mkdir($logDir, 0775, true);
        @file_put_contents($logFile,
            sprintf("[%s] [%s] %s\n", date('c'), $scope, $msg),
            FILE_APPEND
        );
    }
}

function render_card(string $file): string
{
    // Flag lets bootstrap files know they're running inside a sandbox
    if (!defined('CARD_SANDBOX')) {
        define('CARD_SANDBOX', true);
    }

    ob_start();

    // Convert all PHP errors to ErrorException we can catch
    set_error_handler(
        fn($sev, $msg, $f, $l) => throw new ErrorException($msg, 0, $sev, $f, $l)
    );

    try {
        include $file;                     // run the actual card
    } catch (Throwable $e) {
        // Log & return graceful placeholder
        log_debug('CARD', basename($file) . ': ' . $e->getMessage());
        return '<div class="card-error" style="
                    background:rgba(255,0,0,.05);
                    border-radius:12px;
                    padding:1rem;
                    font-size:.9rem;
                    font-style:italic;
                    color:#ff6b6b;
                    text-align:center;
                ">
                    ⚠ ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '
                </div>';
    } finally {
        restore_error_handler();
    }

    return ob_get_clean();                 // normal rendered card
}
