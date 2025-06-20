<?php declare(strict_types=1);
/**
 * /api/log_client_error.php
 * Accepts JSON payload: { "msg": "...", "url": "...", "line": 123 }
 * Writes a JS-error line to logs/debug.log and returns 204 No Content.
 * Fully self-contained per project rules (manual .env parsing, no includes).
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

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$msg  = $payload['msg']  ?? 'unknown';
$url  = $payload['url']  ?? '';
$line = $payload['line'] ?? '';

error_log('[JS] ' . $msg . " @ {$url}:{$line}");
http_response_code(204);   // No Content — nothing to return
exit;
