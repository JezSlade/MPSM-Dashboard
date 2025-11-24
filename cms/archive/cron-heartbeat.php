#!/usr/bin/env php
<?php
/**
 * CRON Heartbeat Test
 *
 * Simple script that just writes a timestamp when executed.
 * Add as temporary CRON job to verify CRON is working:
 * * * * * * /usr/bin/php /home/resolut7/public_html/cms/cron-heartbeat.php
 */

$heartbeatFile = __DIR__ . '/logs/cron-heartbeat.txt';
$timestamp = date('Y-m-d H:i:s');

// Ensure logs directory exists
if (!is_dir(dirname($heartbeatFile))) {
    mkdir(dirname($heartbeatFile), 0755, true);
}

// Append timestamp
file_put_contents(
    $heartbeatFile,
    "[{$timestamp}] CRON heartbeat - PHP " . PHP_VERSION . " - SAPI: " . php_sapi_name() . "\n",
    FILE_APPEND
);

// Also try to write to a direct file to test permissions
$testFile = __DIR__ . '/last-cron-run.txt';
file_put_contents($testFile, $timestamp);

echo "Heartbeat logged: {$timestamp}\n";
