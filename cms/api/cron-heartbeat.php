<?php
/**
 * CRON Heartbeat Logger
 *
 * Add this to the CRON command to log execution:
 * /usr/local/bin/php -d detect_unicode=0 /path/to/cron-heartbeat.php && /usr/local/bin/php -d detect_unicode=0 /path/to/refresh-cache-chunked.php process
 */

$logFile = __DIR__ . '/../logs/cron-heartbeat.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$timestamp = date('Y-m-d H:i:s');
$message = "[{$timestamp}] CRON executed\n";

file_put_contents($logFile, $message, FILE_APPEND);

// Also echo for CRON email
echo $message;
