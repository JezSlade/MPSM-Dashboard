<?php declare(strict_types=1);
// includes/logger.php
// -------------------------------------------------------------------
// Logs API requests to logs/api.log.
// No header or output -- pure server‐side logging.
// -------------------------------------------------------------------

function log_request(): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $entry = [
        'time'   => date('c'),
        'method' => $_SERVER['REQUEST_METHOD']   ?? '',
        'uri'    => $_SERVER['REQUEST_URI']      ?? '',
        'get'    => $_GET,
        'post'   => json_decode(file_get_contents('php://input'), true),
        'ip'     => $_SERVER['REMOTE_ADDR']      ?? '',
    ];

    file_put_contents(
        $logDir . '/api.log',
        json_encode($entry) . "\n",
        FILE_APPEND
    );
}
