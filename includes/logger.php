<?php
// includes/logger.php

/**
 * Append each request (URI + body + timestamp) to a rotating log.
 */

function log_request(): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/api.log';
    $entry   = [
        'time'    => date('c'),
        'method'  => $_SERVER['REQUEST_METHOD'],
        'uri'     => $_SERVER['REQUEST_URI'],
        'get'     => $_GET,
        'post'    => json_decode(file_get_contents('php://input'), true),
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];
    file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
}
