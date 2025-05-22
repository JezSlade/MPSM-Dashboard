<?php
// core/Logger.php
// v1.0.0 — PSR-3–style shim forwarding to debug_log()

require_once __DIR__ . '/debug.php';

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        debug_log($message, $context, 'INFO');
    }

    public static function debug(string $message, array $context = []): void
    {
        debug_log($message, $context, 'DEBUG');
    }

    public static function error(string $message, array $context = []): void
    {
        debug_log($message, $context, 'ERROR');
    }
}
